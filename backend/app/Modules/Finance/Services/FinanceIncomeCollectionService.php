<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinanceAccount;
use App\Modules\Finance\Models\FinanceAccountLedgerEntry;
use App\Modules\Finance\Models\FinanceAccountTypeTransaction;
use App\Modules\Finance\Models\FinanceIncomeCollection;
use App\Modules\Finance\Models\IncomeCategory;
use App\Modules\Finance\Models\IncomeHead;
use App\Modules\Finance\Repositories\FinanceIncomeCollectionRepository;
use App\Services\BaseCachedService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FinanceIncomeCollectionService extends BaseCachedService
{
    public function __construct(
        protected FinanceIncomeCollectionRepository $repository,
        private readonly FinanceAccountService $accountService,
        private readonly FinanceAccountTypeTransactionService $typeTransactionService,
    ) {
        parent::__construct(new FinanceIncomeCollection());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getSummary(array $filters = []): array
    {
        return $this->remember(
            $this->filtersCacheKey([...$filters, '_summary' => true]),
            fn () => $this->repository->getSummary($filters)
        );
    }

    /**
     * Income recognized when earned. Cash/bank receipts that settle a prior due
     * are excluded so the income statement does not double-count.
     *
     * @return array<int, float>
     */
    public function getEarnedAmountsByIncomeHead(?string $fromDate = null, ?string $toDate = null): array
    {
        $query = FinanceIncomeCollection::query()
            ->whereIn('status', ['collected', 'due']);

        if (!empty($fromDate)) {
            $query->whereDate('collection_date', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $query->whereDate('collection_date', '<=', $toDate);
        }

        $rows = $query->get([
            'id',
            'income_head_id',
            'amount',
            'payment_method',
            'settles_income_collection_id',
            'candidates',
            'linked_account_id',
        ]);

        $dueRows = FinanceIncomeCollection::query()
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->get(['id', 'income_head_id', 'candidates', 'linked_account_id']);

        $totals = [];
        foreach ($rows as $row) {
            if ($this->collectionSettlesPriorDue($row, $dueRows)) {
                continue;
            }

            $headId = (int) $row->income_head_id;
            if ($headId <= 0) {
                continue;
            }

            $totals[$headId] = round(($totals[$headId] ?? 0) + (float) $row->amount, 2);
        }

        return $totals;
    }

    public function collect(array $data): FinanceIncomeCollection
    {
        return $this->mutate(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $categoryId = (int) ($data['category_id'] ?? $data['income_category_id'] ?? 0);
                $headId = (int) ($data['head_id'] ?? $data['income_head_id'] ?? 0);
                $amount = round((float) ($data['amount'] ?? 0), 2);
                $billedAmount = round((float) ($data['billed_amount'] ?? 0), 2);
                $collectionDate = (string) ($data['collection_date'] ?? now()->toDateString());
                $paymentMethod = strtolower((string) ($data['payment_method'] ?? 'cash'));
                $particular = trim((string) ($data['particular'] ?? ''));
                $referenceNo = trim((string) ($data['reference_no'] ?? ''));
                $remarks = trim((string) ($data['remarks'] ?? ''));
                $receiveAccountId = (int) ($data['receive_account_id'] ?? $data['main_account_id'] ?? 0);
                $hasCandidatePayload = is_array($data['candidates'] ?? null) && $data['candidates'] !== [];
                $isDue = $paymentMethod === 'due';
                $isOperatingDue = $isDue && !$hasCandidatePayload;

                if ($isOperatingDue) {
                    if ($billedAmount <= 0 && $amount > 0) {
                        $billedAmount = $amount;
                    }
                    if ($billedAmount <= 0) {
                        throw ValidationException::withMessages([
                            'billed_amount' => ['Please enter a valid billed income amount.'],
                        ]);
                    }
                    $amount = 0.0;
                } elseif ($amount <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => ['Please enter a valid amount.'],
                    ]);
                }

                $settlesCollectionId = (int) ($data['settles_income_collection_id'] ?? 0);
                if ($settlesCollectionId > 0 && in_array($paymentMethod, ['cash', 'bank', 'expense_link', 'adjustment'], true)) {
                    $remaining = $this->resolveDueCollectionRemaining($settlesCollectionId, $data);
                    if ($remaining <= 0) {
                        throw ValidationException::withMessages([
                            'settles_income_collection_id' => ['This due receivable is already fully settled.'],
                        ]);
                    }
                    if ($amount > $remaining + 0.0001) {
                        throw ValidationException::withMessages([
                            'amount' => ["Receive amount cannot exceed remaining due of {$remaining}."],
                        ]);
                    }
                }

                if (!in_array($paymentMethod, ['cash', 'bank', 'due', 'expense_link', 'adjustment'], true)) {
                    throw ValidationException::withMessages([
                        'payment_method' => ['Please select a valid receive method (Cash, Bank, Due, Expense Link, or Adjustment).'],
                    ]);
                }

                $isDue = $paymentMethod === 'due';
                $isExpenseLink = $paymentMethod === 'expense_link';
                $isAdjustment = $paymentMethod === 'adjustment';
                $postPaymentCredit = !$isDue;
                $methodLabel = match ($paymentMethod) {
                    'cash' => 'Cash',
                    'bank' => 'Bank',
                    'due' => 'Due',
                    'expense_link' => 'Expense Link',
                    'adjustment' => 'Adjustment',
                    default => ucfirst($paymentMethod),
                };

                $category = IncomeCategory::query()->find($categoryId);
                $head = IncomeHead::query()->find($headId);

                if (!$category || $category->status !== 'active') {
                    throw ValidationException::withMessages([
                        'category_id' => ['Please select a valid active income category.'],
                    ]);
                }

                if (!$head || (int) $head->income_category_id !== $categoryId || $head->status !== 'active') {
                    throw ValidationException::withMessages([
                        'head_id' => ['Please select a valid income head for the selected category.'],
                    ]);
                }

                if ($particular === '') {
                    $particular = "{$category->name} - {$head->name}";
                }

                $receiveAccount = null;
                $expenseAccount = null;
                $liabilityAccount = null;
                if (in_array($paymentMethod, ['cash', 'bank'], true)) {
                    $receiveAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->find($receiveAccountId);

                    if (!$receiveAccount || $receiveAccount->category !== 'main' || $receiveAccount->status !== 'active') {
                        throw ValidationException::withMessages([
                            'receive_account_id' => ['Please select an active main account to receive income.'],
                        ]);
                    }

                    $expectedType = $paymentMethod === 'bank' ? 'Bank' : 'Cash';
                    if (strcasecmp((string) $receiveAccount->account_type, $expectedType) !== 0) {
                        throw ValidationException::withMessages([
                            'receive_account_id' => ["Please select a {$expectedType} main account for {$methodLabel} receive method."],
                        ]);
                    }
                } elseif ($isExpenseLink) {
                    $expenseAccount = $this->accountService->resolveBillsReceivableLinkedExpenseAccount();
                } elseif ($isAdjustment) {
                    $liabilityAccountId = (int) ($data['liability_account_id'] ?? $receiveAccountId);
                    $liabilityAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->find($liabilityAccountId);

                    if (!$liabilityAccount || $liabilityAccount->category !== 'liabilities' || $liabilityAccount->status !== 'active') {
                        throw ValidationException::withMessages([
                            'liability_account_id' => ['Please select an active liabilities account for adjustment.'],
                        ]);
                    }
                }

                $linkedAccount = null;
                $linkedAccountId = (int) ($data['linked_account_id'] ?? 0);
                if ($linkedAccountId > 0) {
                    $linkedAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->find($linkedAccountId);

                    if (!$linkedAccount || $linkedAccount->status !== 'active') {
                        throw ValidationException::withMessages([
                            'linked_account_id' => ['Please select a valid active linked account.'],
                        ]);
                    }
                }

                $incomeAccount = $this->resolveIncomeAccountForCollection($headId, $linkedAccount);
                if ($incomeAccount) {
                    $incomeAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->find($incomeAccount->id);
                }

                $voucherPrefix = $this->resolveIncomeVoucherPrefix($category);
                $voucherNo = $referenceNo !== ''
                    ? $referenceNo
                    : $this->nextVoucherNo($voucherPrefix, Carbon::parse($collectionDate));

                $typeTransactionPayload = [
                    'transaction_type' => 'income_collection',
                    'amount' => $isOperatingDue ? $billedAmount : $amount,
                    'transaction_date' => $collectionDate,
                    'particular' => $particular,
                    'reference_no' => $referenceNo ?: null,
                    'remarks' => $remarks ?: null,
                    'voucher_no' => $voucherNo,
                ];

                if ($isDue && $linkedAccount) {
                    $typeTransaction = FinanceAccountTypeTransaction::query()->create([
                        ...$typeTransactionPayload,
                        'account_category' => $linkedAccount->category,
                        'main_account_type' => '',
                        'account_id' => $linkedAccount->id,
                        'account_label' => $this->accountLabel($linkedAccount),
                    ]);
                } elseif ($isDue && $incomeAccount) {
                    $typeTransaction = FinanceAccountTypeTransaction::query()->create([
                        ...$typeTransactionPayload,
                        'account_category' => $incomeAccount->category,
                        'main_account_type' => '',
                        'account_id' => $incomeAccount->id,
                        'account_label' => $this->accountLabel($incomeAccount),
                    ]);
                } elseif ($receiveAccount) {
                    $typeTransaction = FinanceAccountTypeTransaction::query()->create([
                        ...$typeTransactionPayload,
                        'to_account_category' => $receiveAccount->category,
                        'to_main_account_type' => (string) ($receiveAccount->account_type ?? ''),
                        'to_account_id' => $receiveAccount->id,
                        'to_account_label' => $this->accountLabel($receiveAccount),
                        'from_account_category' => $linkedAccount?->category,
                        'from_account_id' => $linkedAccount?->id,
                        'from_account_label' => $linkedAccount ? $this->accountLabel($linkedAccount) : null,
                        'account_category' => $receiveAccount->category,
                        'main_account_type' => (string) ($receiveAccount->account_type ?? ''),
                        'account_id' => $receiveAccount->id,
                        'account_label' => $this->accountLabel($receiveAccount),
                    ]);
                } elseif ($expenseAccount) {
                    $typeTransaction = FinanceAccountTypeTransaction::query()->create([
                        ...$typeTransactionPayload,
                        'to_account_category' => $expenseAccount->category,
                        'to_account_id' => $expenseAccount->id,
                        'to_account_label' => $this->accountLabel($expenseAccount),
                        'from_account_category' => $linkedAccount?->category,
                        'from_account_id' => $linkedAccount?->id,
                        'from_account_label' => $linkedAccount ? $this->accountLabel($linkedAccount) : null,
                        'account_category' => $expenseAccount->category,
                        'account_id' => $expenseAccount->id,
                        'account_label' => $this->accountLabel($expenseAccount),
                    ]);
                } elseif ($liabilityAccount) {
                    $typeTransaction = FinanceAccountTypeTransaction::query()->create([
                        ...$typeTransactionPayload,
                        'to_account_category' => $liabilityAccount->category,
                        'to_account_id' => $liabilityAccount->id,
                        'to_account_label' => $this->accountLabel($liabilityAccount),
                        'from_account_category' => $linkedAccount?->category,
                        'from_account_id' => $linkedAccount?->id,
                        'from_account_label' => $linkedAccount ? $this->accountLabel($linkedAccount) : null,
                        'account_category' => $liabilityAccount->category,
                        'account_id' => $liabilityAccount->id,
                        'account_label' => $this->accountLabel($liabilityAccount),
                    ]);
                } else {
                    throw ValidationException::withMessages([
                        'linked_account_id' => ['A linked account is required for due income collection.'],
                    ]);
                }

                if ($receiveAccount && $postPaymentCredit) {
                    $this->postCreditLedger(
                        $receiveAccount,
                        $typeTransaction->id,
                        $amount,
                        $collectionDate,
                        $particular,
                        $voucherNo,
                        $linkedAccount ? $this->accountLabel($linkedAccount) : $head->name,
                        $methodLabel,
                        $remarks ?: 'Income collection'
                    );
                }

                if ($expenseAccount && $postPaymentCredit) {
                    $this->postDebitExpenseLinkLedger(
                        $expenseAccount,
                        $typeTransaction->id,
                        $amount,
                        $collectionDate,
                        $particular,
                        $voucherNo,
                        $linkedAccount ? $this->accountLabel($linkedAccount) : $head->name,
                        $methodLabel,
                        $remarks ?: 'Receivable settled via expense link'
                    );
                }

                if ($liabilityAccount && $postPaymentCredit) {
                    $this->postDebitLedger(
                        $liabilityAccount,
                        $typeTransaction->id,
                        $amount,
                        $collectionDate,
                        $particular,
                        $voucherNo,
                        $linkedAccount ? $this->accountLabel($linkedAccount) : $head->name,
                        $methodLabel,
                        $remarks ?: 'Receivable settled via adjustment'
                    );
                }

                $normalizedCandidates = $this->normalizeCandidates($data['candidates'] ?? []);
                $hasClientCandidateBills = $linkedAccount
                    && $linkedAccount->category === 'client'
                    && is_array($normalizedCandidates)
                    && $normalizedCandidates !== [];

                $billedAmount = round((float) ($data['billed_amount'] ?? 0), 2);
                if ($billedAmount <= 0 && is_array($normalizedCandidates) && $normalizedCandidates !== []) {
                    foreach ($normalizedCandidates as $candidate) {
                        if (!is_array($candidate)) {
                            continue;
                        }
                        $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
                        $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
                        $billedAmount = round($billedAmount + ($salePrice > 0 ? $salePrice : $payAmount), 2);
                    }
                }
                if ($billedAmount <= 0) {
                    $billedAmount = $amount;
                }

                $counterpartyAccount = $receiveAccount ?? $expenseAccount ?? $liabilityAccount;
                // Candidate bills settle by application overlap; simple PL income by head + party.
                $settlingPriorDue = $postPaymentCredit && (
                    $settlesCollectionId > 0
                    || (
                        $hasClientCandidateBills
                            ? $this->hasPriorDueIncomeForCandidates($normalizedCandidates, (int) $head->id)
                            : $this->hasPriorDueIncomeForHead((int) $head->id, $linkedAccount?->id)
                    )
                );

                if ($settlingPriorDue && $settlesCollectionId <= 0) {
                    $settlesCollectionId = $this->resolvePriorDueCollectionId(
                        (int) $head->id,
                        $normalizedCandidates,
                        $linkedAccount?->id
                    );
                }

                $billParticular = trim((string) $head->name).' Bill';
                $jobCode = trim((string) ($data['job_code'] ?? '')) ?: null;
                $clientDisplayName = trim((string) ($data['client_name'] ?? ''))
                    ?: ($linkedAccount ? $this->accountLabel($linkedAccount) : '');

                // Selected client ledger mirrors income-head pattern:
                // cash → DR bill + CR receive; due → DR bill; settle → CR receive.
                if ($linkedAccount && $linkedAccount->category === 'client') {
                    if ($isDue) {
                        if ($hasClientCandidateBills) {
                            $this->createClientCandidateLedgerEntries(
                                $linkedAccount,
                                null,
                                $normalizedCandidates,
                                $typeTransaction->id,
                                $collectionDate,
                                $particular,
                                $voucherNo,
                                $jobCode,
                                $remarks,
                                $methodLabel,
                                false,
                                true,
                                $billParticular
                            );
                        } else {
                            $this->postDebitReceivableLedger(
                                $linkedAccount,
                                $typeTransaction->id,
                                $amount,
                                $collectionDate,
                                $billParticular,
                                $voucherNo,
                                $clientDisplayName !== '' ? $clientDisplayName : $head->name,
                                $remarks ?: 'Due bill'
                            );
                        }
                    } elseif ($settlingPriorDue) {
                        if ($hasClientCandidateBills) {
                            $this->createClientCandidateLedgerEntries(
                                $linkedAccount,
                                $counterpartyAccount,
                                $normalizedCandidates,
                                $typeTransaction->id,
                                $collectionDate,
                                $particular,
                                $voucherNo,
                                $jobCode,
                                $remarks,
                                $methodLabel,
                                true,
                                false,
                                $billParticular
                            );
                        } else {
                            $this->postCreditLedger(
                                $linkedAccount,
                                $typeTransaction->id,
                                $isAdjustment ? 0.0 : $amount,
                                $collectionDate,
                                $particular,
                                $voucherNo,
                                $counterpartyAccount
                                    ? $this->accountLabel($counterpartyAccount)
                                    : $head->name,
                                $methodLabel,
                                $remarks ?: 'Receive due payment'
                            );
                        }
                    } elseif ($hasClientCandidateBills) {
                        $this->createClientCandidateLedgerEntries(
                            $linkedAccount,
                            $counterpartyAccount,
                            $normalizedCandidates,
                            $typeTransaction->id,
                            $collectionDate,
                            $particular,
                            $voucherNo,
                            $jobCode,
                            $remarks,
                            $methodLabel,
                            $postPaymentCredit,
                            true,
                            $billParticular
                        );
                    } elseif ($postPaymentCredit) {
                        // Bills Receivable "adjustment" should not re-post any numeric amount on the payer ledger.
                        // Keep the ledger rows but write 0 on both DR/CR so UI renders "-" on both columns.
                        $payerDebitAmount = $isAdjustment ? 0.0 : $billedAmount;
                        $payerCreditAmount = $isAdjustment ? 0.0 : $amount;

                        $this->postDebitReceivableLedger(
                            $linkedAccount,
                            $typeTransaction->id,
                            $payerDebitAmount,
                            $collectionDate,
                            $billParticular,
                            $voucherNo,
                            $clientDisplayName !== '' ? $clientDisplayName : $head->name,
                            $remarks ?: 'Income charge'
                        );
                        $this->postCreditLedger(
                            $linkedAccount,
                            $typeTransaction->id,
                            $payerCreditAmount,
                            $collectionDate,
                            $particular,
                            $voucherNo,
                            $counterpartyAccount
                                ? $this->accountLabel($counterpartyAccount)
                                : $head->name,
                            $methodLabel,
                            $remarks ?: 'Income collection'
                        );
                    }
                } elseif (
                    $linkedAccount
                    && !$isDue
                    && $postPaymentCredit
                    && !$settlingPriorDue
                    && !$this->isIncomeCategoryAccount($linkedAccount)
                    && !$this->isSameAccount($linkedAccount, $incomeAccount)
                ) {
                    $this->postCreditLedger(
                        $linkedAccount,
                        $typeTransaction->id,
                        $isAdjustment ? 0.0 : $amount,
                        $collectionDate,
                        $particular,
                        $voucherNo,
                        $counterpartyAccount
                            ? $this->accountLabel($counterpartyAccount)
                            : $head->name,
                        $methodLabel,
                        $remarks ?: 'Income collection'
                    );
                }

                if ($incomeAccount) {
                    if ($isDue) {
                        // Due bill: DR on Client Commission; AR on income receivable.
                        if ($hasClientCandidateBills) {
                            $this->createIncomeHeadCandidateLedgerEntries(
                                $incomeAccount,
                                null,
                                $normalizedCandidates,
                                $typeTransaction->id,
                                $collectionDate,
                                $particular,
                                $voucherNo,
                                $jobCode,
                                $remarks,
                                $clientDisplayName,
                                $methodLabel,
                                false,
                                true,
                                $billParticular
                            );
                        } else {
                            $this->postDebitReceivableLedger(
                                $incomeAccount,
                                $typeTransaction->id,
                                $isOperatingDue ? $billedAmount : $amount,
                                $collectionDate,
                                $billParticular,
                                $voucherNo,
                                $linkedAccount ? $this->accountLabel($linkedAccount) : $head->name,
                                $remarks ?: 'Due bill'
                            );
                        }
                    } elseif ($settlingPriorDue) {
                        // Receive due payment: CR on Client Commission (TB uses max(DR, CR) — no double count).
                        if ($hasClientCandidateBills) {
                            $this->createIncomeHeadCandidateLedgerEntries(
                                $incomeAccount,
                                $counterpartyAccount,
                                $normalizedCandidates,
                                $typeTransaction->id,
                                $collectionDate,
                                $particular,
                                $voucherNo,
                                $jobCode,
                                $remarks,
                                $clientDisplayName,
                                $methodLabel,
                                true,
                                false,
                                $billParticular
                            );
                        } else {
                            $this->postCreditLedger(
                                $incomeAccount,
                                $typeTransaction->id,
                                $amount,
                                $collectionDate,
                                $particular,
                                $voucherNo,
                                $counterpartyAccount
                                    ? $this->accountLabel($counterpartyAccount)
                                    : $head->name,
                                $methodLabel,
                                $remarks ?: 'Receive due payment'
                            );
                        }
                    } elseif ($hasClientCandidateBills) {
                        // Cash/bank first collection: DR (bill) + CR (receive).
                        $this->createIncomeHeadCandidateLedgerEntries(
                            $incomeAccount,
                            $counterpartyAccount,
                            $normalizedCandidates,
                            $typeTransaction->id,
                            $collectionDate,
                            $particular,
                            $voucherNo,
                            $jobCode,
                            $remarks,
                            $clientDisplayName,
                            $methodLabel,
                            $postPaymentCredit,
                            true,
                            $billParticular
                        );
                    } elseif ($postPaymentCredit) {
                        // Cash/bank without candidates: DR billed amount (bill) + CR receive.
                        $this->postIncomeHeadBillAndReceive(
                            $incomeAccount,
                            $counterpartyAccount,
                            $billedAmount,
                            $amount,
                            $typeTransaction->id,
                            $collectionDate,
                            $billParticular,
                            $particular,
                            $voucherNo,
                            $linkedAccount ? $this->accountLabel($linkedAccount) : $head->name,
                            $methodLabel,
                            $remarks
                        );
                    }
                } elseif (
                    $linkedAccount
                    && $this->isIncomeCategoryAccount($linkedAccount)
                    && $isDue
                    && !$hasClientCandidateBills
                ) {
                    $this->postDebitReceivableLedger(
                        $linkedAccount,
                        $typeTransaction->id,
                        $isOperatingDue ? $billedAmount : $amount,
                        $collectionDate,
                        $billParticular,
                        $voucherNo,
                        $head->name,
                        $remarks ?: 'Due bill'
                    );
                } elseif (
                    $linkedAccount
                    && $this->isIncomeCategoryAccount($linkedAccount)
                    && $postPaymentCredit
                    && !$isDue
                    && !$settlingPriorDue
                    && !$hasClientCandidateBills
                ) {
                    $this->postIncomeHeadBillAndReceive(
                        $linkedAccount,
                        $counterpartyAccount,
                        $billedAmount,
                        $amount,
                        $typeTransaction->id,
                        $collectionDate,
                        $billParticular,
                        $particular,
                        $voucherNo,
                        $head->name,
                        $methodLabel,
                        $remarks
                    );
                }

                // Receivable ledger: DR on due, CR when Bills Receivable receive payment settles it.
                if ($isDue) {
                    $this->accountService->recordIncomeReceivableEntry(
                        $head,
                        $isOperatingDue ? $billedAmount : $amount,
                        'dr',
                        $collectionDate,
                        $voucherNo,
                        $typeTransaction->id,
                        $billParticular,
                        $methodLabel,
                        $remarks ?: 'Due receivable',
                        $linkedAccount ? $this->accountLabel($linkedAccount) : ($head->name ?? '')
                    );
                } elseif ($settlingPriorDue) {
                    $this->accountService->recordIncomeReceivableEntry(
                        $head,
                        $amount,
                        'cr',
                        $collectionDate,
                        $voucherNo,
                        $typeTransaction->id,
                        $particular !== '' ? $particular : 'Receivable settled',
                        $methodLabel,
                        $remarks ?: 'Receivable settled',
                        $linkedAccount ? $this->accountLabel($linkedAccount) : ($head->name ?? '')
                    );
                }

                $collection = FinanceIncomeCollection::query()->create([
                    'income_category_id' => $category->id,
                    'income_head_id' => $head->id,
                    'job_list_id' => !empty($data['job_list_id'] ?? $data['job_id'] ?? null)
                        ? (int) ($data['job_list_id'] ?? $data['job_id'])
                        : null,
                    'job_code' => trim((string) ($data['job_code'] ?? '')) ?: null,
                    'job_title' => trim((string) ($data['job_title'] ?? '')) ?: null,
                    'client_name' => trim((string) ($data['client_name'] ?? '')) ?: null,
                    'candidates' => $normalizedCandidates,
                    'amount' => $isOperatingDue ? $billedAmount : $amount,
                    'payment_method' => $paymentMethod,
                    'collection_date' => $collectionDate,
                    'particular' => $particular,
                    'reference_no' => $referenceNo ?: null,
                    'voucher_no' => $voucherNo,
                    'remarks' => $remarks ?: null,
                    'status' => $isDue ? 'due' : 'collected',
                    'linked_account_category' => $data['linked_account_category'] ?? $linkedAccount?->category,
                    'linked_account_id' => $linkedAccount?->id,
                    'linked_account_name' => $data['linked_account_name'] ?? ($linkedAccount ? $this->accountLabel($linkedAccount) : null),
                    'linked_account_type' => $data['linked_account_type'] ?? $linkedAccount?->account_type,
                    'receive_account_category' => $receiveAccount?->category
                        ?? $expenseAccount?->category
                        ?? $liabilityAccount?->category,
                    'receive_account_type' => $receiveAccount?->account_type,
                    'receive_account_id' => $receiveAccount?->id
                        ?? $expenseAccount?->id
                        ?? $liabilityAccount?->id,
                    'receive_account_name' => $receiveAccount
                        ? $this->accountLabel($receiveAccount)
                        : ($expenseAccount
                            ? $this->accountLabel($expenseAccount)
                            : ($liabilityAccount ? $this->accountLabel($liabilityAccount) : null)),
                    'finance_account_type_transaction_id' => $typeTransaction->id,
                    'settles_income_collection_id' => $settlesCollectionId > 0 ? $settlesCollectionId : null,
                    'collected_by_id' => $data['collected_by_id'] ?? (Auth::user()?->getAuthIdentifier()),
                    'collected_by_name' => $data['collected_by_name'] ?? (Auth::user()?->name),
                ]);

                if (
                    !$isDue
                    && !$settlingPriorDue
                    && in_array($paymentMethod, ['cash', 'bank', 'expense_link', 'adjustment'], true)
                    && round($billedAmount - $amount, 2) > 0.005
                ) {
                    $this->createDueRemainderIncomeCollection(
                        $collection,
                        $head,
                        $billedAmount,
                        $amount,
                        $normalizedCandidates,
                        $linkedAccount
                    );
                }

                $this->accountService->flushCache();
                $this->typeTransactionService->flushCache();

                return $collection->load(['incomeCategory', 'incomeHead']);
            });
        });
    }

    /**
     * Backfill Client Commission / income-head ledger:
     * - due bill: DR particular "{Income Head} Bill"
     * - cash (no prior due): DR (Bill) + CR (receive)
     * - settle prior due: CR receive only (TB uses max(DR, CR))
     */
    public function backfillMissingIncomeHeadCredits(): void
    {
        // Keep client due bill DRs — they mirror Client Commission Bill on the party ledger.
        $this->normalizeDueIncomeHeadEntries();

        $collections = FinanceIncomeCollection::query()
            ->with('incomeHead.incomeCategory')
            ->where(function ($query) {
                foreach (['cash', 'bank', 'expense_link', 'adjustment', 'due'] as $method) {
                    $query->orWhereRaw('LOWER(COALESCE(payment_method, "")) = ?', [$method]);
                }
            })
            ->orderBy('id')
            ->get();

        foreach ($collections as $collection) {
            $head = $collection->incomeHead;
            if (!$head) {
                continue;
            }

            $incomeAccount = $this->accountService->ensureIncomeHeadAccount($head);
            if (!$incomeAccount) {
                continue;
            }

            $amount = round((float) ($collection->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $typeTransactionId = (int) ($collection->finance_account_type_transaction_id ?? 0);
            $voucherNo = trim((string) ($collection->voucher_no ?? $collection->reference_no ?? ''));
            $method = strtolower((string) ($collection->payment_method ?? ''));
            $settlesId = (int) ($collection->settles_income_collection_id ?? 0);
            $candidates = is_array($collection->candidates) ? $collection->candidates : [];
            $linkedAccountId = $collection->linked_account_id ? (int) $collection->linked_account_id : null;
            $hasCandidates = is_array($candidates) && $candidates !== [];

            $settlingPriorDue = in_array($method, ['cash', 'bank', 'expense_link', 'adjustment'], true) && (
                $settlesId > 0
                || (
                    $hasCandidates
                        ? $this->hasPriorDueIncomeForCandidates($candidates, (int) $head->id)
                        : $this->hasPriorDueIncomeForHead((int) $head->id, $linkedAccountId)
                )
            );

            $chargeAmount = $amount;
            if ($candidates !== []) {
                $chargeAmount = 0.0;
                foreach ($candidates as $candidate) {
                    if (!is_array($candidate)) {
                        continue;
                    }
                    $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
                    $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
                    $chargeAmount = round($chargeAmount + ($salePrice > 0 ? $salePrice : $payAmount), 2);
                }
            }
            if ($chargeAmount <= 0) {
                $chargeAmount = $amount;
            }

            $hasDr = $this->incomeHeadEntryExists($incomeAccount->id, $typeTransactionId, $voucherNo, 'dr');
            $hasCr = $this->incomeHeadEntryExists($incomeAccount->id, $typeTransactionId, $voucherNo, 'cr');

            $methodLabel = match ($method) {
                'cash' => 'Cash',
                'bank' => 'Bank',
                'due' => 'Due',
                'expense_link' => 'Expense Link',
                'adjustment' => 'Adjustment',
                default => ucfirst((string) ($collection->payment_method ?? '')),
            };

            $particular = trim((string) ($collection->particular ?? ''));
            if ($particular === '') {
                $particular = trim(($collection->incomeCategory?->name ?? 'Income').' - '.$head->name);
            }

            $clientName = (string) ($collection->linked_account_name ?: $collection->client_name ?: '');
            $entryDate = (string) ($collection->collection_date ?? now()->toDateString());

            // Settle prior due: CR only on income head.
            if ($settlingPriorDue) {
                if (!$hasCr && $amount > 0) {
                    $this->createLedgerEntry([
                        'finance_account_id' => $incomeAccount->id,
                        'finance_account_type_transaction_id' => $typeTransactionId ?: null,
                        'entry_date' => $entryDate,
                        'particular' => $particular,
                        'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
                        'client_name' => $clientName !== '' ? $clientName : null,
                        'dr_amount' => 0,
                        'cr_amount' => $amount,
                        'payment_method' => $methodLabel,
                        'remarks' => $collection->remarks ?: 'Receive due payment',
                    ]);

                    $incomeAccount->update([
                        'balance' => round((float) $incomeAccount->balance + $amount, 2),
                    ]);
                }
                continue;
            }

            // Due or first cash: Bill on DR.
            if (!$hasDr && $chargeAmount > 0) {
                $billParticular = $this->formatIncomeBillParticular(
                    (string) $head->name,
                    $clientName,
                    $candidates
                );
                $this->createLedgerEntry([
                    'finance_account_id' => $incomeAccount->id,
                    'finance_account_type_transaction_id' => $typeTransactionId ?: null,
                    'entry_date' => $entryDate,
                    'particular' => $billParticular,
                    'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
                    'client_name' => $clientName !== '' ? $clientName : null,
                    'dr_amount' => $chargeAmount,
                    'cr_amount' => 0,
                    'payment_method' => null,
                    'remarks' => $collection->remarks ?: $billParticular,
                ]);
            }

            // Cash/bank receive CR only when not settling a prior due.
            if (
                in_array($method, ['cash', 'bank', 'expense_link', 'adjustment'], true)
                && !$hasCr
                && $amount > 0
            ) {
                $this->createLedgerEntry([
                    'finance_account_id' => $incomeAccount->id,
                    'finance_account_type_transaction_id' => $typeTransactionId ?: null,
                    'entry_date' => $entryDate,
                    'particular' => $particular,
                    'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
                    'client_name' => $clientName !== '' ? $clientName : null,
                    'dr_amount' => 0,
                    'cr_amount' => $amount,
                    'payment_method' => $methodLabel,
                    'remarks' => $collection->remarks ?: 'Income collection backfill',
                ]);

                $incomeAccount->update([
                    'balance' => round((float) $incomeAccount->balance + $amount, 2),
                ]);
            }
        }
    }

    private function incomeHeadEntryExists(
        int $incomeAccountId,
        int $typeTransactionId,
        string $voucherNo,
        string $side
    ): bool {
        $amountColumn = $side === 'dr' ? 'dr_amount' : 'cr_amount';

        return FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $incomeAccountId)
            ->where($amountColumn, '>', 0)
            ->where(function ($query) use ($typeTransactionId, $voucherNo) {
                if ($typeTransactionId > 0) {
                    $query->where('finance_account_type_transaction_id', $typeTransactionId);
                }
                if ($voucherNo !== '') {
                    $query->orWhere('voucher_no', $voucherNo)
                        ->orWhere('voucher_no', 'like', $voucherNo.'-%');
                }
            })
            ->exists();
    }

    /**
     * Due bill must be DR with "{Income Head} Bill". Convert older due CRs to DR.
     */
    private function normalizeDueIncomeHeadEntries(): void
    {
        $dueRows = FinanceIncomeCollection::query()
            ->with('incomeHead')
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->get();

        foreach ($dueRows as $row) {
            $head = $row->incomeHead;
            if (!$head) {
                continue;
            }

            $incomeAccount = $this->accountService->ensureIncomeHeadAccount($head);
            if (!$incomeAccount) {
                continue;
            }

            $typeTransactionId = (int) ($row->finance_account_type_transaction_id ?? 0);
            $voucherNo = trim((string) ($row->voucher_no ?? ''));
            $amount = round((float) ($row->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $candidates = is_array($row->candidates) ? $row->candidates : [];
            $chargeAmount = $amount;
            if ($candidates !== []) {
                $chargeAmount = 0.0;
                foreach ($candidates as $candidate) {
                    if (!is_array($candidate)) {
                        continue;
                    }
                    $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
                    $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
                    $chargeAmount = round($chargeAmount + ($salePrice > 0 ? $salePrice : $payAmount), 2);
                }
                if ($chargeAmount <= 0) {
                    $chargeAmount = $amount;
                }
            }

            $matchedEntries = $this->findIncomeHeadEntriesForVoucher(
                (int) $incomeAccount->id,
                $typeTransactionId,
                $voucherNo
            );

            $keptDr = null;
            foreach ($matchedEntries as $entry) {
                $crAmount = round((float) $entry->cr_amount, 2);
                $drAmount = round((float) $entry->dr_amount, 2);

                // Convert any due CR into the bill DR (or drop extras).
                if ($crAmount > 0) {
                    $incomeAccount->balance = round((float) $incomeAccount->balance - $crAmount, 2);
                    $entry->delete();
                    continue;
                }

                if ($drAmount > 0) {
                    $billParticular = $this->formatIncomeBillParticular(
                        (string) $head->name,
                        (string) ($entry->client_name ?: $row->linked_account_name ?: $row->client_name ?: ''),
                        is_array($candidates) ? $candidates : []
                    );
                    if ($keptDr === null) {
                        $entry->particular = $billParticular;
                        $entry->payment_method = null;
                        $entry->dr_amount = round((float) $entry->dr_amount, 2) > 0
                            ? round((float) $entry->dr_amount, 2)
                            : $chargeAmount;
                        $entry->cr_amount = 0;
                        $entry->remarks = $row->remarks ?: $billParticular;
                        $entry->save();
                        $keptDr = $entry;
                    } else {
                        // Keep per-candidate bill rows; only refresh particular text.
                        $entry->particular = $billParticular;
                        $entry->payment_method = null;
                        $entry->remarks = $row->remarks ?: $billParticular;
                        $entry->save();
                    }
                }
            }

            if ($keptDr === null) {
                $billParticular = $this->formatIncomeBillParticular(
                    (string) $head->name,
                    (string) ($row->linked_account_name ?: $row->client_name ?: ''),
                    is_array($candidates) ? $candidates : []
                );
                $this->createLedgerEntry([
                    'finance_account_id' => $incomeAccount->id,
                    'finance_account_type_transaction_id' => $typeTransactionId ?: null,
                    'entry_date' => (string) ($row->collection_date ?? now()->toDateString()),
                    'particular' => $billParticular,
                    'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
                    'client_name' => $row->linked_account_name ?: $row->client_name,
                    'dr_amount' => $chargeAmount,
                    'cr_amount' => 0,
                    'payment_method' => null,
                    'remarks' => $row->remarks ?: $billParticular,
                ]);
            }

            $incomeAccount->save();
        }
    }

    private function findIncomeHeadEntriesForVoucher(
        int $incomeAccountId,
        int $typeTransactionId,
        string $voucherNo
    ) {
        return FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $incomeAccountId)
            ->where(function ($query) use ($typeTransactionId, $voucherNo) {
                if ($typeTransactionId > 0) {
                    $query->where('finance_account_type_transaction_id', $typeTransactionId);
                }
                if ($voucherNo !== '') {
                    $query->orWhere(function ($inner) use ($voucherNo) {
                        $inner->where('voucher_no', $voucherNo)
                            ->orWhere('voucher_no', 'like', $voucherNo.'-%');
                    });
                }
            })
            ->get();
    }

    /**
     * Older client-income dues posted Client Commission DR on the client party.
     * Those belong on Client Commission Receivable, not the client TB line.
     */
    private function removeErroneousClientDueDebits(): void
    {
        $dueRows = FinanceIncomeCollection::query()
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->whereNotNull('linked_account_id')
            ->get(['id', 'linked_account_id', 'voucher_no', 'finance_account_type_transaction_id', 'amount']);

        foreach ($dueRows as $row) {
            $clientId = (int) ($row->linked_account_id ?? 0);
            if ($clientId <= 0) {
                continue;
            }

            $voucherNo = trim((string) ($row->voucher_no ?? ''));
            $typeTransactionId = (int) ($row->finance_account_type_transaction_id ?? 0);

            $query = FinanceAccountLedgerEntry::query()
                ->where('finance_account_id', $clientId)
                ->where('dr_amount', '>', 0)
                ->where(function ($inner) use ($typeTransactionId, $voucherNo) {
                    if ($typeTransactionId > 0) {
                        $inner->where('finance_account_type_transaction_id', $typeTransactionId);
                    }
                    if ($voucherNo !== '') {
                        $inner->orWhere('voucher_no', $voucherNo)
                            ->orWhere('voucher_no', 'like', $voucherNo.'-%');
                    }
                });

            $entries = $query->get();
            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

    private function postDebitReceivableLedger(
        FinanceAccount $account,
        int $typeTransactionId,
        float $amount,
        string $entryDate,
        string $particular,
        string $voucherNo,
        string $clientName,
        string $remarks
    ): void {
        $this->createLedgerEntry([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular !== '' ? $particular : 'Due receivable',
            'voucher_no' => $voucherNo,
            'client_name' => $clientName ?: null,
            'dr_amount' => $amount,
            'cr_amount' => 0,
            'payment_method' => null,
            'remarks' => $remarks ?: 'Due receivable',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>|null  $candidates
     */
    private function hasPriorDueIncomeForCandidates(?array $candidates, int $incomeHeadId): bool
    {
        return $this->resolvePriorDueCollectionId($incomeHeadId, $candidates, null) > 0;
    }

    private function hasPriorDueIncomeForHead(int $incomeHeadId, ?int $linkedAccountId): bool
    {
        return $this->resolvePriorDueCollectionId($incomeHeadId, null, $linkedAccountId) > 0;
    }

    /**
     * @param  list<array<string, mixed>>|null  $candidates
     */
    private function resolvePriorDueCollectionId(
        int $incomeHeadId,
        ?array $candidates,
        ?int $linkedAccountId
    ): int {
        if ($incomeHeadId <= 0) {
            return 0;
        }

        $applicationIds = $this->candidateApplicationIds($candidates);

        $query = FinanceIncomeCollection::query()
            ->where('income_head_id', $incomeHeadId)
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->orderByDesc('id');

        if ($applicationIds !== []) {
            $dueRows = $query->whereNotNull('candidates')->get(['id', 'candidates']);
            foreach ($dueRows as $row) {
                $dueAppIds = $this->candidateApplicationIds(
                    is_array($row->candidates) ? $row->candidates : []
                );
                if (array_intersect($applicationIds, $dueAppIds) !== []) {
                    return (int) $row->id;
                }
            }

            return 0;
        }

        $query->whereNull('candidates');
        if ($linkedAccountId) {
            $query->where('linked_account_id', $linkedAccountId);
        }

        return (int) ($query->value('id') ?? 0);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FinanceIncomeCollection>  $dueRows
     */
    private function collectionSettlesPriorDue(FinanceIncomeCollection $row, $dueRows): bool
    {
        if ((int) ($row->settles_income_collection_id ?? 0) > 0) {
            return true;
        }

        $method = strtolower((string) ($row->payment_method ?? ''));
        if (!in_array($method, ['cash', 'bank', 'expense_link', 'adjustment'], true)) {
            return false;
        }

        $headId = (int) $row->income_head_id;
        $rowId = (int) $row->id;
        $applicationIds = $this->candidateApplicationIds(
            is_array($row->candidates) ? $row->candidates : []
        );
        $linkedAccountId = $row->linked_account_id ? (int) $row->linked_account_id : null;

        foreach ($dueRows as $due) {
            if ((int) $due->id >= $rowId) {
                continue;
            }
            if ((int) $due->income_head_id !== $headId) {
                continue;
            }

            $dueAppIds = $this->candidateApplicationIds(
                is_array($due->candidates) ? $due->candidates : []
            );

            if ($applicationIds !== []) {
                if (array_intersect($applicationIds, $dueAppIds) !== []) {
                    return true;
                }

                continue;
            }

            if ($dueAppIds !== []) {
                continue;
            }

            $dueLinkedId = $due->linked_account_id ? (int) $due->linked_account_id : null;
            if ($linkedAccountId) {
                if ($dueLinkedId === $linkedAccountId) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>|null  $candidates
     * @return list<int>
     */
    private function candidateApplicationIds(?array $candidates): array
    {
        if (!is_array($candidates) || $candidates === []) {
            return [];
        }

        $applicationIds = [];
        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            if ($applicationId > 0) {
                $applicationIds[] = $applicationId;
            }
        }

        return array_values(array_unique($applicationIds));
    }

    private function postDebitExpenseLinkLedger(
        FinanceAccount $account,
        int $typeTransactionId,
        float $amount,
        string $entryDate,
        string $particular,
        string $voucherNo,
        string $clientName,
        string $paymentMethod,
        string $remarks
    ): void {
        $this->createLedgerEntry([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo,
            'client_name' => $clientName ?: null,
            'dr_amount' => $amount,
            'cr_amount' => 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks ?: null,
        ]);
    }

    private function postDebitLedger(
        FinanceAccount $account,
        int $typeTransactionId,
        float $amount,
        string $entryDate,
        string $particular,
        string $voucherNo,
        string $clientName,
        string $paymentMethod,
        string $remarks
    ): void {
        $this->createLedgerEntry([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo,
            'client_name' => $clientName ?: null,
            'dr_amount' => $amount,
            'cr_amount' => 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks ?: null,
        ]);

        $account->update([
            'balance' => round((float) $account->balance - $amount, 2),
        ]);
    }

    private function postCreditLedger(
        FinanceAccount $account,
        int $typeTransactionId,
        float $amount,
        string $entryDate,
        string $particular,
        string $voucherNo,
        string $clientName,
        string $paymentMethod,
        string $remarks
    ): void {
        $this->createLedgerEntry([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo,
            'client_name' => $clientName ?: null,
            'dr_amount' => 0,
            'cr_amount' => $amount,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks ?: null,
        ]);

        $account->update([
            'balance' => round((float) $account->balance + $amount, 2),
        ]);
    }

    /**
     * Per-candidate client ledger (mirrors Client Commission income-head):
     * - due: DR bill only
     * - cash first: DR bill + CR receive
     * - settle due: CR receive only
     */
    private function createClientCandidateLedgerEntries(
        FinanceAccount $client,
        ?FinanceAccount $receiveAccount,
        array $candidates,
        int $typeTransactionId,
        string $collectionDate,
        string $particular,
        string $voucherNo,
        ?string $jobCode,
        string $remarks,
        string $paymentMethodLabel = 'Cash',
        bool $postPaymentCredit = true,
        bool $postChargeDebit = true,
        string $billParticular = ''
    ): void {
        $billParticular = trim($billParticular) !== ''
            ? trim($billParticular)
            : 'Client Commission Bill';

        $applicationIds = [];
        foreach ($candidates as $candidate) {
            $applicationId = (int) ($candidate['application_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId > 0 && $payAmount > 0) {
                $applicationIds[] = $applicationId;
            }
        }

        $alreadyBilledLookup = $this->getAlreadyBilledClientApplicationIds($applicationIds);

        foreach ($candidates as $index => $candidate) {
            $applicationId = (int) ($candidate['application_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId <= 0 || $payAmount <= 0) {
                continue;
            }

            $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
            $candidateName = trim((string) ($candidate['candidate_name'] ?? ''));
            $passportNo = trim((string) ($candidate['passport_no'] ?? ''));
            $candidateLabel = trim($candidateName.($passportNo !== '' ? " ({$passportNo})" : ''));
            $candidateVoucherNo = $voucherNo !== ''
                ? sprintf('%s-%d', $voucherNo, $index + 1)
                : (string) $applicationId;

            $chargeAmount = $salePrice > 0 ? $salePrice : $payAmount;
            $isFirstBill = !isset($alreadyBilledLookup[$applicationId]);

            if ($postChargeDebit && $isFirstBill && $chargeAmount > 0) {
                $billLineParticular = $candidateLabel !== ''
                    ? "{$billParticular} for {$candidateLabel}"
                    : $billParticular;

                $this->createLedgerEntry([
                    'finance_account_id' => $client->id,
                    'finance_account_type_transaction_id' => $typeTransactionId,
                    'entry_date' => $collectionDate,
                    'particular' => $billLineParticular,
                    'voucher_no' => $candidateVoucherNo,
                    'job' => $jobCode,
                    'client_name' => $candidateLabel !== '' ? $candidateLabel : null,
                    'dr_amount' => $paymentMethodLabel === 'Adjustment' ? 0 : $chargeAmount,
                    'cr_amount' => 0,
                    'payment_method' => null,
                    'remarks' => $billLineParticular,
                ]);
            }

            if (!$postPaymentCredit || $payAmount <= 0) {
                continue;
            }

            $receiveLabel = $receiveAccount ? $this->accountLabel($receiveAccount) : $this->accountLabel($client);

            $this->createLedgerEntry([
                'finance_account_id' => $client->id,
                'finance_account_type_transaction_id' => $typeTransactionId,
                'entry_date' => $collectionDate,
                'particular' => $particular,
                'voucher_no' => $candidateVoucherNo,
                'job' => $jobCode,
                'client_name' => $candidateLabel !== ''
                    ? $candidateLabel
                    : $receiveLabel,
                'dr_amount' => 0,
                'cr_amount' => $paymentMethodLabel === 'Adjustment' ? 0 : $payAmount,
                'payment_method' => $paymentMethodLabel,
                'remarks' => $remarks !== ''
                    ? $remarks
                    : ($candidateLabel !== '' ? "Payment collection for {$candidateLabel}" : null),
            ]);
        }
    }

    /**
     * Per-candidate income head ledger:
     * - due bill: DR (sale/charge) only
     * - cash first: DR (bill) + CR (receive)
     * - settle due: CR (receive) only
     */
    private function createIncomeHeadCandidateLedgerEntries(
        FinanceAccount $incomeAccount,
        ?FinanceAccount $receiveAccount,
        array $candidates,
        int $typeTransactionId,
        string $collectionDate,
        string $particular,
        string $voucherNo,
        ?string $jobCode,
        string $remarks,
        string $clientName,
        string $paymentMethodLabel = 'Cash',
        bool $postPaymentCredit = true,
        bool $postChargeDebit = true,
        string $billParticular = ''
    ): void {
        $billParticular = trim($billParticular) !== ''
            ? trim($billParticular)
            : (trim((string) ($incomeAccount->account_name ?? '')).' Bill');
        if ($billParticular === ' Bill') {
            $billParticular = 'Client Commission Bill';
        }

        $applicationIds = [];
        foreach ($candidates as $candidate) {
            $applicationId = (int) ($candidate['application_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId > 0 && $payAmount > 0) {
                $applicationIds[] = $applicationId;
            }
        }

        $alreadyBilledLookup = $this->getAlreadyBilledClientApplicationIds($applicationIds);
        $balanceDelta = 0.0;

        foreach ($candidates as $index => $candidate) {
            $applicationId = (int) ($candidate['application_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId <= 0 || $payAmount <= 0) {
                continue;
            }

            $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
            $candidateName = trim((string) ($candidate['candidate_name'] ?? ''));
            $passportNo = trim((string) ($candidate['passport_no'] ?? ''));
            $candidateLabel = trim($candidateName.($passportNo !== '' ? " ({$passportNo})" : ''));
            $displayName = $candidateLabel !== '' ? $candidateLabel : ($clientName ?: null);
            $candidateVoucherNo = $voucherNo !== ''
                ? sprintf('%s-%d', $voucherNo, $index + 1)
                : (string) $applicationId;

            $chargeAmount = $salePrice > 0 ? $salePrice : $payAmount;
            $isFirstBill = !isset($alreadyBilledLookup[$applicationId]);

            if ($postChargeDebit && $isFirstBill && $chargeAmount > 0) {
                $billLineParticular = $candidateLabel !== ''
                    ? "{$billParticular} for {$candidateLabel}"
                    : $billParticular;

                $this->createLedgerEntry([
                    'finance_account_id' => $incomeAccount->id,
                    'finance_account_type_transaction_id' => $typeTransactionId,
                    'entry_date' => $collectionDate,
                    'particular' => $billLineParticular,
                    'voucher_no' => $candidateVoucherNo,
                    'job' => $jobCode,
                    'client_name' => $displayName,
                    'dr_amount' => $chargeAmount,
                    'cr_amount' => 0,
                    'payment_method' => null,
                    'remarks' => $billLineParticular,
                ]);
            }

            if (!$postPaymentCredit || $payAmount <= 0) {
                continue;
            }

            $receiveLabel = $receiveAccount ? $this->accountLabel($receiveAccount) : $clientName;

            $this->createLedgerEntry([
                'finance_account_id' => $incomeAccount->id,
                'finance_account_type_transaction_id' => $typeTransactionId,
                'entry_date' => $collectionDate,
                'particular' => $particular,
                'voucher_no' => $candidateVoucherNo,
                'job' => $jobCode,
                'client_name' => $displayName ?: $receiveLabel,
                'dr_amount' => 0,
                'cr_amount' => $payAmount,
                'payment_method' => $paymentMethodLabel,
                'remarks' => $remarks !== ''
                    ? $remarks
                    : ($candidateLabel !== '' ? "Receive payment for {$candidateLabel}" : 'Income collection'),
            ]);

            $balanceDelta += $payAmount;
        }

        if ($balanceDelta > 0) {
            $incomeAccount->update([
                'balance' => round((float) $incomeAccount->balance + $balanceDelta, 2),
            ]);
        }
    }

    private function getAlreadyBilledClientApplicationIds(array $applicationIds): array
    {
        $applicationIds = array_values(array_unique(array_filter($applicationIds)));
        if ($applicationIds === []) {
            return [];
        }

        $lookup = [];
        $target = array_fill_keys($applicationIds, true);

        FinanceIncomeCollection::query()
            ->whereNotNull('candidates')
            ->orderByDesc('id')
            ->chunkById(100, function ($rows) use (&$lookup, $target) {
                foreach ($rows as $row) {
                    $candidates = $row->candidates;
                    if (!is_array($candidates)) {
                        continue;
                    }

                    foreach ($candidates as $candidate) {
                        if (!is_array($candidate)) {
                            continue;
                        }

                        $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
                        if ($applicationId > 0 && isset($target[$applicationId])) {
                            $lookup[$applicationId] = true;
                        }
                    }
                }
            });

        return $lookup;
    }

    private function createLedgerEntry(array $attributes): void
    {
        FinanceAccountLedgerEntry::query()->create([
            'discount' => 0,
            ...$attributes,
        ]);
    }

    private function formatIncomeBillParticular(
        string $headName,
        string $fallbackName = '',
        array $candidates = []
    ): string {
        $base = trim($headName) !== '' ? trim($headName).' Bill' : 'Client Commission Bill';

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            $candidateName = trim((string) ($candidate['candidate_name'] ?? ''));
            $passportNo = trim((string) ($candidate['passport_no'] ?? ''));
            if ($candidateName === '') {
                continue;
            }
            $label = $candidateName.($passportNo !== '' ? " ({$passportNo})" : '');

            return "{$base} for {$label}";
        }

        $fallbackName = trim($fallbackName);
        if ($fallbackName !== '') {
            return "{$base} for {$fallbackName}";
        }

        return $base;
    }

    private function resolveIncomeVoucherPrefix(IncomeCategory $category): string
    {
        $code = strtolower(trim((string) ($category->code ?? '')));

        return match ($code) {
            'client_income' => 'CC',
            'other_income' => 'OI',
            'recruitment_income' => 'RI',
            default => 'INC',
        };
    }

    private function nextVoucherNo(string $prefix, Carbon $date): string
    {
        $yearSuffix = $date->format('y');

        $count = FinanceIncomeCollection::query()
            ->where('voucher_no', 'like', $prefix.'-%')
            ->where('voucher_no', 'like', '%/'.$yearSuffix)
            ->count() + 1;

        // Short format: CC-1/26 (no long date stamp).
        return sprintf('%s-%d/%s', $prefix, $count, $yearSuffix);
    }

    private function normalizeCandidates(mixed $candidates): ?array
    {
        if (!is_array($candidates) || $candidates === []) {
            return null;
        }

        $normalized = [];

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            if ($applicationId <= 0) {
                continue;
            }

            $normalized[] = [
                'application_id' => $applicationId,
                'candidate_name' => trim((string) ($candidate['candidate_name'] ?? '')) ?: null,
                'passport_no' => trim((string) ($candidate['passport_no'] ?? '')) ?: null,
                'sale_price' => round((float) ($candidate['sale_price'] ?? 0), 2),
                'amount' => round((float) ($candidate['amount'] ?? 0), 2),
            ];
        }

        return $normalized !== [] ? $normalized : null;
    }

    private function resolveIncomeAccountForCollection(int $headId, ?FinanceAccount $linkedAccount): ?FinanceAccount
    {
        if ($headId <= 0) {
            return null;
        }

        $head = IncomeHead::query()->with('incomeCategory')->find($headId);
        if (!$head) {
            return null;
        }

        return $this->accountService->ensureIncomeHeadAccount($head);
    }

    private function isSameAccount(?FinanceAccount $left, ?FinanceAccount $right): bool
    {
        return $left && $right && (int) $left->id === (int) $right->id;
    }

    private function isIncomeCategoryAccount(?FinanceAccount $account): bool
    {
        if (!$account) {
            return false;
        }

        return in_array(
            (string) $account->category,
            ['recruitment_income', 'client_income', 'other_income'],
            true
        );
    }

    private function postIncomeHeadBillAndReceive(
        FinanceAccount $incomeAccount,
        ?FinanceAccount $counterpartyAccount,
        float $billedAmount,
        float $receivedAmount,
        int $typeTransactionId,
        string $collectionDate,
        string $billParticular,
        string $receiveParticular,
        string $voucherNo,
        string $counterpartyLabel,
        string $methodLabel,
        string $remarks
    ): void {
        $billedAmount = round($billedAmount, 2);
        $receivedAmount = round($receivedAmount, 2);

        if ($billedAmount > 0) {
            $this->postDebitReceivableLedger(
                $incomeAccount,
                $typeTransactionId,
                $billedAmount,
                $collectionDate,
                $billParticular,
                $voucherNo,
                $counterpartyLabel,
                $remarks ?: 'Income charge'
            );
        }

        if ($receivedAmount <= 0) {
            return;
        }

        $this->postCreditLedger(
            $incomeAccount,
            $typeTransactionId,
            $receivedAmount,
            $collectionDate,
            $receiveParticular,
            $voucherNo,
            $counterpartyAccount
                ? $this->accountLabel($counterpartyAccount)
                : $counterpartyLabel,
            $methodLabel,
            $remarks ?: 'Income collection'
        );
    }

    private function accountLabel(FinanceAccount $account): string
    {
        $code = trim((string) ($account->account_code ?? ''));
        $name = trim((string) ($account->account_name ?? $account->name ?? ''));

        if ($code !== '' && $name !== '') {
            return "{$code} — {$name}";
        }

        return $name !== '' ? $name : ($code !== '' ? $code : "Account #{$account->id}");
    }

    /**
     * After a partial cash/bank receive, raise the leftover billed amount as due
     * so it appears on Bills Receivable (client candidates or operating income).
     *
     * @param  list<array<string, mixed>>|null  $candidates
     */
    private function createDueRemainderIncomeCollection(
        FinanceIncomeCollection $cashCollection,
        IncomeHead $head,
        float $billedAmount,
        float $receivedAmount,
        ?array $candidates,
        ?FinanceAccount $linkedAccount
    ): void {
        $billedAmount = round($billedAmount, 2);
        $receivedAmount = round($receivedAmount, 2);

        if ($billedAmount <= $receivedAmount + 0.005) {
            return;
        }

        $collectionDate = (string) ($cashCollection->collection_date ?? now()->toDateString());
        $voucherNo = trim((string) ($cashCollection->voucher_no ?? $cashCollection->reference_no ?? ''));
        $remainderVoucher = $voucherNo !== ''
            ? sprintf('%s-R', $voucherNo)
            : sprintf('INC-R-%d', $cashCollection->id);
        $dueRemarks = trim((string) ($cashCollection->remarks ?? ''))
            ?: 'Partial receive — remaining moved to Bills Receivable';
        $typeTransactionId = (int) ($cashCollection->finance_account_type_transaction_id ?? 0) ?: null;

        $remainderCandidates = [];
        $remaining = 0.0;

        if (is_array($candidates) && $candidates !== []) {
            $applicationIds = $this->candidateApplicationIds($candidates);
            $summaryById = [];
            foreach ($this->getIncomeCollectionSummary($applicationIds) as $row) {
                $summaryById[(int) $row['application_id']] = $row;
            }

            foreach ($candidates as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
                if ($applicationId <= 0) {
                    continue;
                }

                $summary = $summaryById[$applicationId] ?? null;
                if ((bool) ($summary['has_due'] ?? false)) {
                    continue;
                }

                $salePrice = round((float) ($candidate['sale_price'] ?? $summary['sale_price'] ?? 0), 2);
                $collected = round((float) ($summary['collected_amount'] ?? 0), 2);
                $candidateRemaining = round(max($salePrice - $collected, 0), 2);
                if ($candidateRemaining <= 0.005) {
                    continue;
                }

                $remainderCandidates[] = [
                    ...$candidate,
                    'sale_price' => $salePrice,
                    'amount' => $candidateRemaining,
                ];
                $remaining = round($remaining + $candidateRemaining, 2);
            }
        } else {
            $remaining = round(max($billedAmount - $receivedAmount, 0), 2);
            if ($remaining > 0.005 && $this->hasPriorDueIncomeForHead((int) $head->id, $linkedAccount?->id)) {
                $remaining = 0.0;
            }
        }

        if ($remaining <= 0.005) {
            return;
        }

        FinanceIncomeCollection::query()->create([
            'income_category_id' => $cashCollection->income_category_id,
            'income_head_id' => $cashCollection->income_head_id,
            'job_list_id' => $cashCollection->job_list_id,
            'job_code' => $cashCollection->job_code,
            'job_title' => $cashCollection->job_title,
            'client_name' => $cashCollection->client_name,
            'candidates' => $remainderCandidates !== [] ? $remainderCandidates : null,
            'amount' => $remaining,
            'payment_method' => 'due',
            'collection_date' => $collectionDate,
            'particular' => $cashCollection->particular,
            'reference_no' => $cashCollection->reference_no,
            'voucher_no' => $remainderVoucher,
            'remarks' => $dueRemarks,
            'status' => 'due',
            'linked_account_category' => $cashCollection->linked_account_category,
            'linked_account_id' => $cashCollection->linked_account_id,
            'linked_account_name' => $cashCollection->linked_account_name,
            'linked_account_type' => $cashCollection->linked_account_type,
            'receive_account_category' => null,
            'receive_account_type' => null,
            'receive_account_id' => null,
            'receive_account_name' => null,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'settles_income_collection_id' => null,
            'collected_by_id' => $cashCollection->collected_by_id,
            'collected_by_name' => $cashCollection->collected_by_name,
        ]);

        $this->accountService->recordIncomeReceivableEntry(
            $head,
            $remaining,
            'dr',
            $collectionDate,
            $remainderVoucher,
            $typeTransactionId,
            trim((string) $head->name).' Bill',
            'Due',
            $dueRemarks,
            (string) ($cashCollection->linked_account_name ?: $cashCollection->client_name ?: $head->name)
        );
    }

    /**
     * @param  list<int>  $applicationIds
     * @return list<array{application_id: int, sale_price: float, collected_amount: float, has_due: bool, receivable_remaining: float}>
     */
    public function getIncomeCollectionSummary(array $applicationIds): array
    {
        if ($applicationIds === []) {
            return [];
        }

        $target = array_fill_keys($applicationIds, true);
        $summary = [];

        FinanceIncomeCollection::query()
            ->whereNotNull('candidates')
            ->orderByDesc('id')
            ->chunkById(100, function ($rows) use (&$summary, $target) {
                foreach ($rows as $row) {
                    $candidates = $row->candidates;
                    if (!is_array($candidates)) {
                        continue;
                    }

                    $method = strtolower((string) ($row->payment_method ?? 'cash'));

                    foreach ($candidates as $candidate) {
                        if (!is_array($candidate)) {
                            continue;
                        }

                        $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
                        if ($applicationId <= 0 || !isset($target[$applicationId])) {
                            continue;
                        }

                        if (!isset($summary[$applicationId])) {
                            $summary[$applicationId] = [
                                'application_id' => $applicationId,
                                'sale_price' => round((float) ($candidate['sale_price'] ?? 0), 2),
                                'collected_amount' => 0.0,
                                'has_due' => false,
                            ];
                        }

                        $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
                        if ($salePrice > 0) {
                            $summary[$applicationId]['sale_price'] = $salePrice;
                        }

                        if ($method === 'due') {
                            $summary[$applicationId]['has_due'] = true;
                            $dueAmount = round((float) ($candidate['amount'] ?? 0), 2);
                            if ($dueAmount > 0 && (float) $summary[$applicationId]['sale_price'] <= 0) {
                                $summary[$applicationId]['sale_price'] = $dueAmount;
                            }
                            continue;
                        }

                        $summary[$applicationId]['collected_amount'] = round(
                            (float) $summary[$applicationId]['collected_amount'] + (float) ($candidate['amount'] ?? 0),
                            2
                        );
                    }
                }
            });

        foreach ($summary as &$row) {
            $row['receivable_remaining'] = round(
                max((float) $row['sale_price'] - (float) $row['collected_amount'], 0),
                2
            );
        }
        unset($row);

        return array_values($summary);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listDueReceivables(): array
    {
        $dueRows = FinanceIncomeCollection::query()
            ->with(['incomeCategory', 'incomeHead'])
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->whereNotNull('candidates')
            ->orderByDesc('id')
            ->get();

        if ($dueRows->isEmpty()) {
            return [];
        }

        $applicationIds = [];
        foreach ($dueRows as $row) {
            $candidates = $row->candidates;
            if (!is_array($candidates)) {
                continue;
            }

            foreach ($candidates as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }

                $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
                if ($applicationId > 0) {
                    $applicationIds[] = $applicationId;
                }
            }
        }

        $applicationIds = array_values(array_unique($applicationIds));
        $summaryById = [];
        foreach ($this->getIncomeCollectionSummary($applicationIds) as $row) {
            $summaryById[(int) $row['application_id']] = $row;
        }

        $items = [];
        $seen = [];

        foreach ($dueRows as $dueRow) {
            $candidates = $dueRow->candidates;
            if (!is_array($candidates)) {
                continue;
            }

            foreach ($candidates as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }

                $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
                if ($applicationId <= 0 || isset($seen[$applicationId])) {
                    continue;
                }

                $summary = $summaryById[$applicationId] ?? null;
                if (!$summary || !(bool) ($summary['has_due'] ?? false)) {
                    continue;
                }

                $remaining = round((float) ($summary['receivable_remaining'] ?? 0), 2);
                if ($remaining <= 0) {
                    continue;
                }

                $seen[$applicationId] = true;
                $salePrice = round((float) ($summary['sale_price'] ?? $candidate['sale_price'] ?? 0), 2);
                $collected = round((float) ($summary['collected_amount'] ?? 0), 2);

                $items[] = [
                    'id' => $applicationId,
                    'application_id' => $applicationId,
                    'collection_id' => (int) $dueRow->id,
                    'source' => 'pl_income',
                    'income_category_id' => (int) $dueRow->income_category_id,
                    'income_category_name' => $dueRow->incomeCategory?->name,
                    'income_head_id' => (int) $dueRow->income_head_id,
                    'income_head_name' => $dueRow->incomeHead?->name,
                    'candidate_name' => $candidate['candidate_name'] ?? null,
                    'passport_no' => $candidate['passport_no'] ?? null,
                    'sale_price' => $salePrice,
                    'collected_amount' => $collected,
                    'receivable_remaining' => $remaining,
                    'paid_amount' => $collected,
                    'amount' => $salePrice,
                    'payer_type' => 'client',
                    'party_account_id' => $dueRow->linked_account_id ? (int) $dueRow->linked_account_id : null,
                    'party_account_label' => $dueRow->linked_account_name,
                    'job_list_id' => $dueRow->job_list_id ? (int) $dueRow->job_list_id : null,
                    'job_code' => $dueRow->job_code,
                    'job_title' => $dueRow->job_title,
                    'collection_date' => optional($dueRow->collection_date)?->format('Y-m-d'),
                    'entry_no' => $dueRow->voucher_no,
                    'voucher_no' => $dueRow->voucher_no,
                    'remarks' => $dueRow->remarks,
                    'payment_method' => 'due',
                    'client_name' => $dueRow->client_name,
                ];
            }
        }

        return $items;
    }

    /**
     * Remaining due for a prior income collection being settled.
     * Simple PL income uses collection-level remaining; candidate bills use
     * application remaining so they are not treated as already settled.
     */
    private function resolveDueCollectionRemaining(int $collectionId, array $data = []): float
    {
        if ($collectionId <= 0) {
            return 0.0;
        }

        $dueRow = FinanceIncomeCollection::query()->find($collectionId);
        if (!$dueRow || strtolower((string) ($dueRow->payment_method ?? '')) !== 'due') {
            return 0.0;
        }

        $dueCandidates = is_array($dueRow->candidates) ? $dueRow->candidates : [];
        if ($dueCandidates === []) {
            $summary = $this->getSimpleDueCollectionSummary($collectionId);

            return round((float) ($summary['receivable_remaining'] ?? 0), 2);
        }

        $payloadCandidates = $this->normalizeCandidates($data['candidates'] ?? []);
        $applicationIds = $this->candidateApplicationIds($payloadCandidates);
        if ($applicationIds === []) {
            $applicationIds = $this->candidateApplicationIds($dueCandidates);
        }

        $remaining = 0.0;
        foreach ($this->getIncomeCollectionSummary($applicationIds) as $row) {
            $remaining = round($remaining + (float) ($row['receivable_remaining'] ?? 0), 2);
        }

        return $remaining;
    }

    /**
     * @return array{collection_id: int, sale_price: float, collected_amount: float, receivable_remaining: float, has_due: bool}
     */
    public function getSimpleDueCollectionSummary(int $collectionId): array
    {
        if ($collectionId <= 0) {
            return [];
        }

        $dueRow = FinanceIncomeCollection::query()->find($collectionId);
        if (
            !$dueRow
            || strtolower((string) ($dueRow->payment_method ?? '')) !== 'due'
            || $dueRow->candidates !== null
        ) {
            return [];
        }

        $dueAmount = round((float) $dueRow->amount, 2);
        $collected = round(
            (float) FinanceIncomeCollection::query()
                ->where('settles_income_collection_id', $collectionId)
                ->whereIn('payment_method', ['cash', 'bank', 'expense_link', 'adjustment'])
                ->sum('amount'),
            2
        );

        return [
            'collection_id' => $collectionId,
            'sale_price' => $dueAmount,
            'collected_amount' => $collected,
            'receivable_remaining' => round(max($dueAmount - $collected, 0), 2),
            'has_due' => true,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listSimpleDueReceivables(): array
    {
        $dueRows = FinanceIncomeCollection::query()
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->whereNull('candidates')
            ->with(['incomeCategory', 'incomeHead'])
            ->orderByDesc('id')
            ->get();

        $items = [];

        foreach ($dueRows as $dueRow) {
            $item = $this->buildSimpleDueReceivableItem($dueRow);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSimpleDueReceivable(int $collectionId): ?array
    {
        if ($collectionId <= 0) {
            return null;
        }

        $dueRow = FinanceIncomeCollection::query()
            ->with(['incomeCategory', 'incomeHead'])
            ->find($collectionId);

        if (!$dueRow) {
            return null;
        }

        return $this->buildSimpleDueReceivableItem($dueRow);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildSimpleDueReceivableItem(FinanceIncomeCollection $dueRow): ?array
    {
        if (
            strtolower((string) ($dueRow->payment_method ?? '')) !== 'due'
            || $dueRow->candidates !== null
        ) {
            return null;
        }

        $summary = $this->getSimpleDueCollectionSummary((int) $dueRow->id);
        $remaining = round((float) ($summary['receivable_remaining'] ?? 0), 2);
        if ($remaining <= 0) {
            return null;
        }

        $dueAmount = round((float) ($summary['sale_price'] ?? 0), 2);
        $collected = round((float) ($summary['collected_amount'] ?? 0), 2);
        $collectionId = (int) $dueRow->id;
        $categoryName = $dueRow->incomeCategory?->name;
        $headName = $dueRow->incomeHead?->name;

        return [
            'id' => -$collectionId,
            'application_id' => null,
            'collection_id' => $collectionId,
            'receivable_kind' => 'income_collection',
            'source' => 'pl_income',
            'income_category_id' => (int) $dueRow->income_category_id,
            'income_category_name' => $categoryName,
            'income_head_id' => (int) $dueRow->income_head_id,
            'income_head_name' => $headName,
            'candidate_name' => $headName,
            'passport_no' => null,
            'particular' => $dueRow->particular,
            'sale_price' => $dueAmount,
            'collected_amount' => $collected,
            'receivable_remaining' => $remaining,
            'paid_amount' => $collected,
            'amount' => $dueAmount,
            'payer_type' => $dueRow->linked_account_category ?: 'income',
            'party_account_id' => $dueRow->linked_account_id ? (int) $dueRow->linked_account_id : null,
            'party_account_label' => $dueRow->linked_account_name,
            'linked_account_category' => $dueRow->linked_account_category,
            'job_list_id' => $dueRow->job_list_id ? (int) $dueRow->job_list_id : null,
            'job_code' => $dueRow->job_code,
            'job_title' => $dueRow->job_title,
            'collection_date' => optional($dueRow->collection_date)?->format('Y-m-d'),
            'entry_no' => $dueRow->voucher_no,
            'voucher_no' => $dueRow->voucher_no,
            'remarks' => $dueRow->remarks,
            'payment_method' => 'due',
            'client_name' => $dueRow->client_name,
        ];
    }
}
