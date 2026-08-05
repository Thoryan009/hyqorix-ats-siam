<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinanceAccount;
use App\Modules\Finance\Models\FinanceAccountLedgerEntry;
use App\Modules\Finance\Models\FinanceAccountTypeTransaction;
use App\Modules\Finance\Repositories\FinanceAccountTypeTransactionRepository;
use App\Services\BaseCachedService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceAccountTypeTransactionService extends BaseCachedService
{
    private const ADJUSTMENT_TYPES = ['adjust_minus', 'adjust_plus'];

    public function __construct(
        protected FinanceAccountTypeTransactionRepository $repository,
        private readonly FinanceAccountService $accountService,
    ) {
        parent::__construct(new FinanceAccountTypeTransaction());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function submitTransaction(array $data): FinanceAccountTypeTransaction
    {
        return $this->mutate(function () use ($data) {
            $transactionType = (string) ($data['transaction_type'] ?? '');
            $amount = round((float) ($data['amount'] ?? 0), 2);
            $transactionDate = (string) ($data['transaction_date'] ?? $data['date'] ?? now()->toDateString());
            $particular = trim((string) ($data['particular'] ?? ''));
            $referenceNo = trim((string) ($data['reference_no'] ?? ''));
            $remarks = trim((string) ($data['remarks'] ?? ''));

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Please enter a valid amount.'],
                ]);
            }

            if (in_array($transactionType, self::ADJUSTMENT_TYPES, true)) {
                return $this->submitAdjustment(
                    $transactionType,
                    $amount,
                    $transactionDate,
                    $particular,
                    $referenceNo,
                    $remarks,
                    $data
                );
            }

            return $this->submitTransfer(
                $transactionType,
                $amount,
                $transactionDate,
                $particular,
                $referenceNo,
                $remarks,
                $data
            );
        });
    }

    private function submitAdjustment(
        string $transactionType,
        float $amount,
        string $transactionDate,
        string $particular,
        string $referenceNo,
        string $remarks,
        array $data
    ): FinanceAccountTypeTransaction {
        $category = (string) ($data['account_category'] ?? '');
        $accountId = (int) ($data['account_id'] ?? 0);

        return DB::transaction(function () use (
            $transactionType,
            $amount,
            $transactionDate,
            $particular,
            $referenceNo,
            $remarks,
            $category,
            $data,
            $accountId
        ) {
            $account = $this->resolveAccount($accountId, $category);
            $delta = $transactionType === 'adjust_minus' ? -$amount : $amount;
            $this->assertSufficientBalance($account, $delta);

            $accountLabel = $this->accountLabel($account);
            $resolvedParticular = $particular !== '' ? $particular : $this->defaultParticular($transactionType);
            $voucherNo = $referenceNo !== '' ? $referenceNo : $this->nextVoucherNo($account->id, $this->voucherPrefix($transactionType), $transactionDate);
            $paymentMethod = $this->resolvePaymentMethod($category, (string) ($data['main_account_type'] ?? ''));

            $transaction = FinanceAccountTypeTransaction::query()->create([
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'transaction_date' => $transactionDate,
                'particular' => $resolvedParticular,
                'reference_no' => $referenceNo ?: null,
                'remarks' => $remarks ?: null,
                'voucher_no' => $voucherNo,
                'account_category' => $category,
                'main_account_type' => (string) ($data['main_account_type'] ?? ''),
                'account_id' => $account->id,
                'account_label' => $accountLabel,
            ]);

            $this->applyAccountDelta(
                $account,
                $delta,
                $transaction->id,
                $transactionDate,
                $resolvedParticular,
                $voucherNo,
                $accountLabel,
                $paymentMethod,
                $remarks
            );

            $this->accountService->flushCache();

            return $transaction;
        });
    }

    private function submitTransfer(
        string $transactionType,
        float $amount,
        string $transactionDate,
        string $particular,
        string $referenceNo,
        string $remarks,
        array $data
    ): FinanceAccountTypeTransaction {
        $fromCategory = (string) ($data['from_account_category'] ?? '');
        $toCategory = (string) ($data['to_account_category'] ?? '');
        $fromAccountId = (int) ($data['from_account_id'] ?? 0);
        $toAccountId = (int) ($data['to_account_id'] ?? 0);

        if ($fromAccountId === $toAccountId) {
            throw ValidationException::withMessages([
                'to_account_id' => ['From and to accounts must be different.'],
            ]);
        }

        return DB::transaction(function () use (
            $transactionType,
            $amount,
            $transactionDate,
            $particular,
            $referenceNo,
            $remarks,
            $fromCategory,
            $toCategory,
            $data,
            $fromAccountId,
            $toAccountId
        ) {
            $assetAccountId = (int) ($data['asset_account_id'] ?? 0);
            $liabilitiesAccountId = (int) ($data['liabilities_account_id'] ?? 0);
            $ownersEquityAccountId = (int) ($data['owners_equity_account_id'] ?? 0);

            if ($ownersEquityAccountId > 0) {
                $extraCategory = 'owners_equity';
                $extraAccountId = $ownersEquityAccountId;
            } elseif ($assetAccountId > 0) {
                $extraCategory = 'asset';
                $extraAccountId = $assetAccountId;
            } elseif ($liabilitiesAccountId > 0) {
                $extraCategory = 'liabilities';
                $extraAccountId = $liabilitiesAccountId;
            } else {
                $extraCategory = '';
                $extraAccountId = 0;
            }

            $extraAccount = $extraAccountId > 0
                ? $this->resolveAccount($extraAccountId, $extraCategory)
                : null;

            $effectiveTransactionType = $this->resolveEffectiveTransactionType(
                $transactionType,
                $extraCategory
            );
            $displayTransactionType = $extraAccount
                ? (trim((string) $extraAccount->account_name) ?: $effectiveTransactionType)
                : $effectiveTransactionType;

            $fromAccount = $this->resolveAccount($fromAccountId, $fromCategory);
            $toAccount = $this->resolveAccount($toAccountId, $toCategory);
            $direction = strtolower(trim((string) ($data['transaction_direction'] ?? '')));

            if ($extraCategory === 'owners_equity') {
                // Receive (capital in): party CR, cash CR, equity CR.
                // Payment (drawings out): cash DR, party DR, equity DR.
                $signed = $direction === 'payment' ? -$amount : $amount;
                $fromDelta = $signed;
                $toDelta = $signed;
            } elseif ($extraCategory === 'asset' || $extraCategory === 'liabilities') {
                // Payment: cash/from DR (funds out), party/to CR (payment), asset DR / liability DR.
                // Receive: from CR, cash/to CR, asset/liability CR.
                if ($direction === 'payment') {
                    $fromDelta = -$amount;
                    $toDelta = $amount;
                } elseif ($direction === 'receive') {
                    $fromDelta = $amount;
                    $toDelta = $amount;
                } else {
                    ['from_delta' => $fromDelta, 'to_delta' => $toDelta] = $this->resolveTransferEffects(
                        $effectiveTransactionType,
                        $fromCategory,
                        $toCategory,
                        $amount
                    );
                }
            } else {
                ['from_delta' => $fromDelta, 'to_delta' => $toDelta] = $this->resolveTransferEffects(
                    $effectiveTransactionType,
                    $fromCategory,
                    $toCategory,
                    $amount
                );
            }

            $this->assertSufficientBalance($fromAccount, $fromDelta);
            $this->assertSufficientBalance($toAccount, $toDelta);

            $fromLabel = $this->accountLabel($fromAccount);
            $toLabel = $this->accountLabel($toAccount);
            $resolvedParticular = $particular !== '' ? $particular : $this->defaultParticular($effectiveTransactionType);
            $voucherNo = $referenceNo !== '' ? $referenceNo : $this->nextVoucherNo(
                $extraAccount?->id ?? $fromAccount->id,
                $extraAccount ? $this->voucherPrefixForAccount($extraAccount) : $this->voucherPrefix($effectiveTransactionType),
                $transactionDate
            );

            $fromPaymentMethod = $this->resolvePaymentMethod($fromCategory, (string) ($data['from_main_account_type'] ?? ''));
            $toPaymentMethod = $this->resolvePaymentMethod($toCategory, (string) ($data['to_main_account_type'] ?? ''));

            $transaction = FinanceAccountTypeTransaction::query()->create([
                'transaction_type' => $displayTransactionType,
                'amount' => $amount,
                'transaction_date' => $transactionDate,
                'particular' => $resolvedParticular,
                'reference_no' => $referenceNo ?: null,
                'remarks' => $remarks ?: null,
                'voucher_no' => $voucherNo,
                'from_account_category' => $fromCategory,
                'from_main_account_type' => (string) ($data['from_main_account_type'] ?? ''),
                'from_account_id' => $fromAccount->id,
                'from_account_label' => $fromLabel,
                'to_account_category' => $toCategory,
                'to_main_account_type' => (string) ($data['to_main_account_type'] ?? ''),
                'to_account_id' => $toAccount->id,
                'to_account_label' => $toLabel,
            ]);

            $this->applyAccountDelta(
                $fromAccount,
                $fromDelta,
                $transaction->id,
                $transactionDate,
                $resolvedParticular,
                $voucherNo,
                $toLabel,
                $fromPaymentMethod,
                $remarks ?: "Transfer to {$toLabel}"
            );

            $this->applyAccountDelta(
                $toAccount,
                $toDelta,
                $transaction->id,
                $transactionDate,
                $resolvedParticular,
                $voucherNo,
                $fromLabel,
                $toPaymentMethod,
                $remarks ?: "Transfer from {$fromLabel}"
            );

            if ($extraAccount) {
                // Asset: DR on payment/give (default), CR only if explicitly received.
                // Owner's Equity / Liabilities: CR on receive, DR on payment.
                if ($extraCategory === 'asset') {
                    $extraDelta = $direction === 'receive' ? $amount : -$amount;
                } elseif ($extraCategory === 'owners_equity' || $extraCategory === 'liabilities') {
                    $extraDelta = $direction === 'payment' ? -$amount : $amount;
                } else {
                    ['from_delta' => $ignore, 'to_delta' => $extraDelta] = $this->resolveTransferEffects(
                        $effectiveTransactionType,
                        $fromCategory,
                        $extraCategory,
                        $amount
                    );
                    $this->assertSufficientBalance($extraAccount, $extraDelta);
                }

                $extraLabel = $this->accountLabel($extraAccount);
                $extraPaymentMethod = $this->resolvePaymentMethod($extraCategory, '');

                $this->applyAccountDelta(
                    $extraAccount,
                    $extraDelta,
                    $transaction->id,
                    $transactionDate,
                    $resolvedParticular,
                    $voucherNo,
                    $fromLabel,
                    $extraPaymentMethod,
                    $remarks ?: "Transfer to {$extraLabel}"
                );
            }

            $this->accountService->flushCache();

            return $transaction;
        });
    }

    private function resolveEffectiveTransactionType(string $transactionType, string $extraCategory): string
    {
        if ($extraCategory === 'liabilities' || $extraCategory === 'owners_equity') {
            return 'advanced';
        }

        return $transactionType;
    }

    private function resolveAccount(int $accountId, string $expectedCategory): FinanceAccount
    {
        $account = FinanceAccount::query()->lockForUpdate()->find($accountId);

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' => ['Selected account was not found.'],
            ]);
        }

        if ($account->status !== 'active') {
            throw ValidationException::withMessages([
                'account_id' => ['Selected account is not active.'],
            ]);
        }

        if ($expectedCategory !== '' && $account->category !== $expectedCategory) {
            throw ValidationException::withMessages([
                'account_id' => ['Selected account does not belong to the chosen account category.'],
            ]);
        }

        return $account;
    }

    /**
     * @return array{from_delta: float, to_delta: float}
     */
    private function resolveTransferEffects(
        string $transactionType,
        string $fromCategory,
        string $toCategory,
        float $amount
    ): array {
        return match ($transactionType) {
            'loan' => [
                'from_delta' => -$amount,
                'to_delta' => $toCategory === 'agent' ? -$amount : $amount,
            ],
            // Taking advanced from agent into main: cash increases; agent gets CR (liability to agent).
            'advanced' => $this->resolveAdvancedEffects($fromCategory, $toCategory, $amount),
            'loan_repay' => [
                'from_delta' => $fromCategory === 'agent' ? $amount : -$amount,
                'to_delta' => $amount,
            ],
            // Returning advanced to agent: reverse of advanced (cash out, agent DR).
            'advanced_repay' => $this->resolveAdvancedRepayEffects($fromCategory, $toCategory, $amount),
            default => [
                'from_delta' => -$amount,
                'to_delta' => $amount,
            ],
        };
    }

    /**
     * Advanced from Agent → Main: company receives cash (CR on main);
     * agent ledger posts CR because this is a liability to the agent.
     *
     * @return array{from_delta: float, to_delta: float}
     */
    private function resolveAdvancedEffects(
        string $fromCategory,
        string $toCategory,
        float $amount
    ): array {
        if ($fromCategory === 'agent' && $toCategory === 'main') {
            return [
                'from_delta' => $amount,
                'to_delta' => $amount,
            ];
        }

        // Main → Agent: company gives advance to agent (cash out, agent DR / charge).
        if ($fromCategory === 'main' && $toCategory === 'agent') {
            return [
                'from_delta' => -$amount,
                'to_delta' => -$amount,
            ];
        }

        return [
            'from_delta' => -$amount,
            'to_delta' => $amount,
        ];
    }

    /**
     * @return array{from_delta: float, to_delta: float}
     */
    private function resolveAdvancedRepayEffects(
        string $fromCategory,
        string $toCategory,
        float $amount
    ): array {
        // Agent → Main repay: clear agent CR liability (DR), cash goes out.
        if ($fromCategory === 'agent' && $toCategory === 'main') {
            return [
                'from_delta' => -$amount,
                'to_delta' => -$amount,
            ];
        }

        // Main → Agent repay: cash out to agent, agent CR.
        if ($fromCategory === 'main' && $toCategory === 'agent') {
            return [
                'from_delta' => -$amount,
                'to_delta' => $amount,
            ];
        }

        return [
            'from_delta' => -$amount,
            'to_delta' => $amount,
        ];
    }

    private function applyAccountDelta(
        FinanceAccount $account,
        float $delta,
        int $typeTransactionId,
        string $transactionDate,
        string $particular,
        string $voucherNo,
        string $counterLabel,
        string $paymentMethod,
        string $remarks
    ): void {
        if ($delta == 0.0) {
            return;
        }

        $amount = abs($delta);

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $transactionDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo,
            'client_name' => $counterLabel,
            'dr_amount' => $delta < 0 ? $amount : 0,
            'discount' => 0,
            'cr_amount' => $delta > 0 ? $amount : 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks,
        ]);

        $account->update([
            'balance' => round((float) $account->balance + $delta, 2),
        ]);
    }

    private function assertSufficientBalance(FinanceAccount $account, float $delta): void
    {
        if ($delta >= 0) {
            return;
        }

        // Only Cash/Bank (main) must have funds. Party, asset, equity, liability, etc. may go DR.
        if ($account->category !== 'main') {
            return;
        }

        $required = abs($delta);

        if ((float) $account->balance < $required) {
            throw ValidationException::withMessages([
                'amount' => ["Insufficient balance in {$this->accountLabel($account)}."],
            ]);
        }
    }

    private function accountLabel(FinanceAccount $account): string
    {
        $code = trim((string) $account->code);
        $name = trim((string) $account->account_name);

        return $code !== '' ? "{$name} — {$code}" : $name;
    }

    private function resolvePaymentMethod(string $category, string $mainAccountType): string
    {
        if ($category === 'main' && $mainAccountType !== '') {
            return ucfirst(strtolower($mainAccountType));
        }

        return 'Cash';
    }

    private function defaultParticular(string $transactionType): string
    {
        return match ($transactionType) {
            'loan' => 'Loan',
            'advanced' => 'Advanced',
            'loan_repay' => 'Loan Repay',
            'advanced_repay' => 'Advanced Repay',
            'adjust_minus' => 'Adjust -',
            'adjust_plus' => 'Adjust +',
            default => 'Account Transaction',
        };
    }

    private function voucherPrefix(string $transactionType): string
    {
        return match ($transactionType) {
            'loan' => 'LN',
            'advanced' => 'AD',
            'loan_repay' => 'LR',
            'advanced_repay' => 'AR',
            'adjust_minus' => 'AM',
            'adjust_plus' => 'AP',
            default => 'TX',
        };
    }

    private function voucherPrefixForAccount(FinanceAccount $account): string
    {
        $name = trim((string) $account->account_name);

        if ($name === '') {
            return $account->category === 'asset' ? 'AS' : 'LB';
        }

        $parts = preg_split('/[^A-Za-z0-9]+/', strtoupper($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $prefix = '';

        foreach ($parts as $part) {
            $prefix .= substr($part, 0, 1);
        }

        $prefix = substr($prefix, 0, 4);

        return $prefix !== '' ? $prefix : ($account->category === 'asset' ? 'AS' : 'LB');
    }

    private function nextVoucherNo(int $accountId, string $prefix, string $date): string
    {
        $yearSuffix = Carbon::parse($date)->format('y');

        $count = FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $accountId)
            ->where('voucher_no', 'like', "{$prefix}-%")
            ->where('voucher_no', 'like', "%/{$yearSuffix}")
            ->count() + 1;

        return sprintf('%s-%03d/%s', $prefix, $count, $yearSuffix);
    }
}
