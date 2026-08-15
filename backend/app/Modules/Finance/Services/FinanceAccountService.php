<?php

namespace App\Modules\Finance\Services;

use App\Modules\Application\Helpers\ApplicationPresenter;
use App\Modules\Application\Models\Application;
use App\Modules\Finance\Models\ExpenseHead;
use App\Modules\Finance\Models\FinanceAccount;
use App\Modules\Finance\Models\FinanceAccountLedgerEntry;
use App\Modules\Finance\Models\FinanceAccountTransaction;
use App\Modules\Finance\Models\FinanceAccountTypeTransaction;
use App\Modules\Finance\Models\FinanceSaleCollection;
use App\Modules\Finance\Models\FinanceIncomeCollection;
use App\Modules\Finance\Models\FinanceBillEntry;
use App\Modules\Finance\Models\IncomeHead;
use App\Modules\Finance\Repositories\FinanceAccountRepository;
use App\Modules\JobList\Helpers\JobListPayerHelper;
use App\Modules\Vendor\Models\Vendor;
use App\Services\BaseCachedService;
use Illuminate\Validation\ValidationException;

class FinanceAccountService extends BaseCachedService
{
    public const APPLICANT_CATEGORY = 'applicant';
    public const CAPITAL_CATEGORY = 'capital';
    public const CAPITAL_ACCOUNT_CODE = 'CAPITAL';
    public const SALE_CATEGORY = 'sale';
    public const SALE_ACCOUNT_CODE = 'SALE';
    public const BILLS_RECEIVABLE_CATEGORY = 'bills_receivable';
    public const BILLS_RECEIVABLE_ACCOUNT_CODE = 'BILLS_RECEIVABLE';
    public const INCOME_RECEIVABLE_CATEGORY = 'income_receivable';
    public const EXPENSE_PAYABLE_CATEGORY = 'expense_payable';

    /** Party accounts are created with zero balance — no opening amount / main account on create. */
    private const PARTY_ZERO_BALANCE_CATEGORIES = [
        'agent',
        'vendor',
        'principal',
        'client',
        'staff',
        'banks',
        'owners',
    ];

    public function __construct(protected FinanceAccountRepository $repository)
    {
        parent::__construct(new FinanceAccount());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getCategorySummaryWithCache(string $category): array
    {
        return $this->remember(
            "{$this->getCacheTag()}_summary_{$category}",
            fn () => $this->repository->getCategorySummary($category)
        );
    }

    public function getFinanceAccount(FinanceAccount $financeAccount): FinanceAccount
    {
        return $this->remember(
            $this->byIdCacheKey($financeAccount->id),
            fn () => $financeAccount->load(['bank', 'expenseHead', 'expenseCategory', 'incomeHead', 'incomeCategory'])
        );
    }

    public function createFinanceAccount(array $data): FinanceAccount
    {
        return $this->mutate(function () use ($data) {
            if (($data['category'] ?? null) === self::CAPITAL_CATEGORY) {
                throw ValidationException::withMessages([
                    'category' => ['Capital Ledger is system-managed and cannot be created manually.'],
                ]);
            }

            if (($data['category'] ?? null) === self::SALE_CATEGORY) {
                throw ValidationException::withMessages([
                    'category' => ['Sale Ledger is system-managed and cannot be created manually.'],
                ]);
            }

            if (($data['category'] ?? null) === self::BILLS_RECEIVABLE_CATEGORY) {
                throw ValidationException::withMessages([
                    'category' => ['Bills Receivable Ledger is system-managed and cannot be created manually.'],
                ]);
            }

            if (($data['category'] ?? null) === self::INCOME_RECEIVABLE_CATEGORY) {
                throw ValidationException::withMessages([
                    'category' => ['Income Receivable ledgers are system-managed and cannot be created manually.'],
                ]);
            }

            if (($data['category'] ?? null) === self::EXPENSE_PAYABLE_CATEGORY) {
                throw ValidationException::withMessages([
                    'category' => ['Expense Payable ledgers are system-managed and cannot be created manually.'],
                ]);
            }

            $openingAmount = array_key_exists('opening_amount', $data)
                ? (float) $data['opening_amount']
                : null;
            $openingType = $data['opening_amount_type'] ?? null;
            $mainAccountId = !empty($data['main_account_id']) ? (int) $data['main_account_id'] : null;
            unset($data['opening_amount'], $data['opening_amount_type'], $data['main_account_id']);

            $category = (string) ($data['category'] ?? '');
            if (in_array($category, self::PARTY_ZERO_BALANCE_CATEGORIES, true) || $category === 'main') {
                $data['balance'] = 0;
                $data['opening_balance'] = 0;

                return $this->model->create($data);
            }

            $account = $this->model->create($data);

            if ($openingAmount !== null && $openingAmount > 0) {
                return $this->setPartyOpeningAmount($account, $openingAmount, (string) $openingType);
            }

            $openingBalance = (float) ($account->opening_balance ?: $account->balance ?: 0);

            if ($openingBalance > 0) {
                $this->recordOpeningBalanceTransaction($account, $openingBalance);
            }

            return $account;
        });
    }

    /**
     * System Capital account used as the double-entry offset for main-account openings.
     */
    public function ensureCapitalAccount(bool $backfillMissingOffsets = false): FinanceAccount
    {
        $account = $this->model->firstOrNew([
            'category' => self::CAPITAL_CATEGORY,
            'code' => self::CAPITAL_ACCOUNT_CODE,
        ]);

        $account->account_name = 'Capital Ledger';
        $account->account_type = null;
        $account->status = 'active';

        if (!$account->exists) {
            $account->balance = 0;
            $account->opening_balance = 0;
        }

        $account->save();

        if ($backfillMissingOffsets) {
            $this->backfillMissingCapitalOpeningOffsets();
            $account = $account->fresh();
        }

        return $account;
    }

    /**
     * System Sale account — consolidating income ledger for sale collections.
     */
    public function ensureSaleAccount(bool $backfillMissingEntries = false): FinanceAccount
    {
        $account = $this->model->firstOrNew([
            'category' => self::SALE_CATEGORY,
            'code' => self::SALE_ACCOUNT_CODE,
        ]);

        $account->account_name = 'Sale';
        $account->account_type = null;
        $account->status = 'active';

        if (!$account->exists) {
            $account->balance = 0;
            $account->opening_balance = 0;
        }

        $account->save();

        if ($backfillMissingEntries) {
            $this->backfillMissingSaleEntries();
            $account = $account->fresh();
        }

        return $account;
    }

    /**
     * System Bills Receivable ledger — consolidating AR for Sale due bills.
     */
    public function ensureBillsReceivableAccount(bool $backfillMissingEntries = false): FinanceAccount
    {
        $account = $this->model->firstOrNew([
            'category' => self::BILLS_RECEIVABLE_CATEGORY,
            'code' => self::BILLS_RECEIVABLE_ACCOUNT_CODE,
        ]);

        $account->account_name = 'Sale Receivable';
        $account->account_type = null;
        $account->status = 'active';

        if (!$account->exists) {
            $account->balance = 0;
            $account->opening_balance = 0;
        }

        $account->save();

        if ($backfillMissingEntries) {
            $this->backfillMissingBillsReceivableEntries();
            $account = $account->fresh();
        }

        return $account;
    }

    /**
     * Post DR (raise due) or CR (settle) on Bills Receivable Ledger.
     * Asset convention: balance increases with DR (balance = DR − CR for this ledger).
     */
    public function recordBillsReceivableEntry(
        float $amount,
        string $side,
        string $entryDate,
        string $voucherNo,
        ?int $typeTransactionId = null,
        string $particular = 'Bills Receivable',
        string $paymentMethod = '',
        string $remarks = '',
        string $clientName = '',
        string $demandLetter = '',
        string $job = '',
    ): void {
        if ($amount <= 0) {
            return;
        }

        $side = strtolower($side) === 'cr' ? 'cr' : 'dr';
        $account = $this->ensureBillsReceivableAccount();
        $voucherNo = trim($voucherNo);
        $particular = trim($particular) !== '' ? trim($particular) : 'Bills Receivable';
        $amount = round($amount, 2);

        if ($this->hasExistingReceivableSideEntry(
            (int) $account->id,
            $side,
            $amount,
            $typeTransactionId,
            $voucherNo
        )) {
            return;
        }

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
            'demand_letter' => $demandLetter !== '' ? $demandLetter : null,
            'job' => $job !== '' ? $job : null,
            'client_name' => $clientName !== '' ? $clientName : null,
            'dr_amount' => $side === 'dr' ? $amount : 0,
            'discount' => 0,
            'cr_amount' => $side === 'cr' ? $amount : 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks !== '' ? $remarks : null,
        ]);

        $delta = $side === 'dr' ? $amount : -$amount;
        $account->balance = round((float) $account->balance + $delta, 2);
        $account->save();
    }

    /**
     * Per income-head receivable account (Commission Receivable, Bank Interest Receivable, …).
     */
    public function ensureIncomeReceivableAccount(IncomeHead $incomeHead): FinanceAccount
    {
        $incomeHead->loadMissing('incomeCategory');

        return $this->mutate(function () use ($incomeHead) {
            $account = $this->model->firstOrNew([
                'category' => self::INCOME_RECEIVABLE_CATEGORY,
                'income_head_id' => $incomeHead->id,
            ]);

            $headName = trim((string) $incomeHead->name);
            $account->account_name = $this->incomeReceivableAccountName($headName);
            $account->income_category_id = $incomeHead->income_category_id;
            $account->code = 'RECV-IH-'.$incomeHead->id;
            $account->status = 'active';

            if (!$account->exists) {
                $account->balance = 0;
                $account->opening_balance = 0;
            }

            $account->save();

            return $account;
        });
    }

    public function recordIncomeReceivableEntry(
        IncomeHead $incomeHead,
        float $amount,
        string $side,
        string $entryDate,
        string $voucherNo,
        ?int $typeTransactionId = null,
        string $particular = '',
        string $paymentMethod = '',
        string $remarks = '',
        string $clientName = '',
    ): void {
        if ($amount <= 0) {
            return;
        }

        $side = strtolower($side) === 'cr' ? 'cr' : 'dr';
        $account = $this->ensureIncomeReceivableAccount($incomeHead);
        $voucherNo = trim($voucherNo);
        $particular = trim($particular) !== ''
            ? trim($particular)
            : $account->account_name;
        $amount = round($amount, 2);

        if ($this->hasExistingReceivableSideEntry(
            (int) $account->id,
            $side,
            $amount,
            $typeTransactionId,
            $voucherNo
        )) {
            return;
        }

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
            'client_name' => $clientName !== '' ? $clientName : null,
            'dr_amount' => $side === 'dr' ? $amount : 0,
            'discount' => 0,
            'cr_amount' => $side === 'cr' ? $amount : 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks !== '' ? $remarks : null,
        ]);

        $delta = $side === 'dr' ? $amount : -$amount;
        $account->balance = round((float) $account->balance + $delta, 2);
        $account->save();
    }

    /**
     * Idempotent receivable posting:
     * - Prefer type-transaction id (one DR/CR side per payment txn).
     * - Never block a later settlement just because voucher_no was reused
     *   (SE-001/26 collisions were skipping remaining-payment CRs).
     * - Without type txn (legacy backfill), match voucher + amount + side.
     */
    private function hasExistingReceivableSideEntry(
        int $accountId,
        string $side,
        float $amount,
        ?int $typeTransactionId,
        string $voucherNo
    ): bool {
        $amountColumn = $side === 'dr' ? 'dr_amount' : 'cr_amount';

        if ($typeTransactionId) {
            return FinanceAccountLedgerEntry::query()
                ->where('finance_account_id', $accountId)
                ->where('finance_account_type_transaction_id', $typeTransactionId)
                ->where($amountColumn, '>', 0)
                ->exists();
        }

        if ($voucherNo === '') {
            return false;
        }

        return FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $accountId)
            ->where('voucher_no', $voucherNo)
            ->where($amountColumn, $amount)
            ->exists();
    }

    private function incomeReceivableAccountName(string $headName): string
    {
        $name = trim($headName);
        if ($name === '') {
            return 'Income Receivable';
        }

        if (preg_match('/receivable$/i', $name)) {
            return $name;
        }

        // Client Commission / Commission Received → Commission Receivable
        if (preg_match('/^(.+?)\s+received$/i', $name, $matches)) {
            return trim($matches[1]).' Receivable';
        }

        return $name.' Receivable';
    }

    /**
     * Per expense-head payable account (Medical Payable, Air Ticket Payable, …).
     */
    public function ensureExpensePayableAccount(ExpenseHead $expenseHead): FinanceAccount
    {
        $expenseHead->loadMissing('expenseCategory');

        return $this->mutate(function () use ($expenseHead) {
            $account = $this->model->firstOrNew([
                'category' => self::EXPENSE_PAYABLE_CATEGORY,
                'expense_head_id' => $expenseHead->id,
            ]);

            $headName = trim((string) $expenseHead->name);
            $account->account_name = $this->expensePayableAccountName($headName);
            $account->expense_category_id = $expenseHead->expense_category_id;
            $account->code = 'PAY-EH-'.$expenseHead->id;
            $account->status = 'active';

            if (!$account->exists) {
                $account->balance = 0;
                $account->opening_balance = 0;
            }

            $account->save();

            return $account;
        });
    }

    public function recordExpensePayableEntry(
        ExpenseHead $expenseHead,
        float $amount,
        string $side,
        string $entryDate,
        string $voucherNo,
        ?int $typeTransactionId = null,
        string $particular = '',
        string $paymentMethod = '',
        string $remarks = '',
        string $clientName = '',
    ): void {
        if ($amount <= 0) {
            return;
        }

        $side = strtolower($side) === 'dr' ? 'dr' : 'cr';
        $account = $this->ensureExpensePayableAccount($expenseHead);
        $voucherNo = trim($voucherNo);
        $particular = trim($particular) !== ''
            ? trim($particular)
            : $account->account_name;
        $amount = round($amount, 2);

        if ($this->hasExistingReceivableSideEntry(
            (int) $account->id,
            $side,
            $amount,
            $typeTransactionId,
            $voucherNo
        )) {
            return;
        }

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
            'client_name' => $clientName !== '' ? $clientName : null,
            'dr_amount' => $side === 'dr' ? $amount : 0,
            'discount' => 0,
            'cr_amount' => $side === 'cr' ? $amount : 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks !== '' ? $remarks : null,
        ]);

        // Liability: CR increases payable balance; DR decreases it.
        $delta = $side === 'cr' ? $amount : -$amount;
        $account->balance = round((float) $account->balance + $delta, 2);
        $account->save();
    }

    private function expensePayableAccountName(string $headName): string
    {
        $name = trim($headName);
        if ($name === '') {
            return 'Expense Payable';
        }

        if (str_ends_with(strtolower($name), 'payable')) {
            return $name;
        }

        return $name.' Payable';
    }

    /**
     * Per asset-account purchase payable (Office Equipment Payable, …).
     */
    public function ensureAssetPurchasePayableAccount(FinanceAccount $assetAccount): FinanceAccount
    {
        return $this->mutate(function () use ($assetAccount) {
            $account = $this->model->firstOrNew([
                'category' => 'liabilities',
                'code' => 'PAY-AST-'.$assetAccount->id,
            ]);

            $assetName = trim((string) $assetAccount->account_name);
            $account->account_name = $this->assetPurchasePayableAccountName($assetName);
            $account->metadata = array_merge($account->metadata ?? [], [
                'asset_account_id' => $assetAccount->id,
            ]);
            $account->status = 'active';

            if (!$account->exists) {
                $account->balance = 0;
                $account->opening_balance = 0;
            }

            $account->save();

            return $account;
        });
    }

    public function recordAssetPurchasePayableEntry(
        FinanceAccount $assetAccount,
        float $amount,
        string $side,
        string $entryDate,
        string $voucherNo,
        ?int $typeTransactionId = null,
        string $particular = '',
        string $paymentMethod = '',
        string $remarks = '',
        string $clientName = '',
        ?int $billEntryId = null,
    ): void {
        if ($amount <= 0) {
            return;
        }

        $side = strtolower($side) === 'dr' ? 'dr' : 'cr';
        $account = $this->ensureAssetPurchasePayableAccount($assetAccount);
        $voucherNo = trim($voucherNo);
        $particular = trim($particular) !== ''
            ? trim($particular)
            : $account->account_name;
        $amount = round($amount, 2);

        if ($this->hasExistingReceivableSideEntry(
            (int) $account->id,
            $side,
            $amount,
            $typeTransactionId,
            $voucherNo
        )) {
            return;
        }

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $account->id,
            'finance_bill_entry_id' => $billEntryId,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
            'client_name' => $clientName !== '' ? $clientName : null,
            'dr_amount' => $side === 'dr' ? $amount : 0,
            'discount' => 0,
            'cr_amount' => $side === 'cr' ? $amount : 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks !== '' ? $remarks : null,
        ]);

        $delta = $side === 'cr' ? $amount : -$amount;
        $account->balance = round((float) $account->balance + $delta, 2);
        $account->save();
    }

    private function assetPurchasePayableAccountName(string $assetName): string
    {
        $name = trim($assetName);
        if ($name === '') {
            return 'Asset Purchase Payable';
        }

        if (str_ends_with(strtolower($name), 'payable')) {
            return $name;
        }

        return $name.' Payable';
    }

    /**
     * Backfill expense payable CR/DR from approved due bills and partial settlements.
     *
     * Settlement DRs must not be double-posted: live pay-payable already writes
     * `{voucher}-P{txn}` rows. Only post a backfill DR for any unpaid shortfall.
     */
    public function backfillMissingExpensePayableEntries(): void
    {
        // Still-due bills, plus settled former dues (payment_method flips off "due"
        // on full pay — those must still clear leftover CR on the payable ledger).
        $bills = FinanceBillEntry::query()
            ->with('expenseHead')
            ->where('status', 'approved')
            ->where(function ($query) {
                $query
                    ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
                    ->orWhereRaw('COALESCE(paid_amount, 0) > 0');
            })
            ->orderBy('approved_at')
            ->orderBy('id')
            ->get();

        foreach ($bills as $bill) {
            $head = $bill->expenseHead;
            if (!$head) {
                continue;
            }

            $amount = round((float) ($bill->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $voucherNo = trim((string) ($bill->voucher_no ?? $bill->reference_no ?? ''));
            $voucherPrefix = $voucherNo !== '' ? $voucherNo : 'BILL-DUE-'.$bill->id;
            $entryDate = $bill->approved_at?->format('Y-m-d')
                ?: ($bill->payment_date?->format('Y-m-d') ?? now()->toDateString());
            $isStillDue = strtolower(trim((string) ($bill->payment_method ?? ''))) === 'due';
            $paidAmount = round((float) ($bill->paid_amount ?? 0), 2);
            if (!$isStillDue && $paidAmount <= 0) {
                continue;
            }

            $payableAccount = $this->ensureExpensePayableAccount($head);

            if ($isStillDue) {
                $this->recordExpensePayableEntry(
                    $head,
                    $amount,
                    'cr',
                    $entryDate,
                    $voucherPrefix,
                    null,
                    trim((string) ($bill->particular ?? '')) ?: 'Due payable',
                    'Due',
                    (string) ($bill->remarks ?? $bill->approval_remarks ?? ''),
                    (string) ($bill->client_name ?? $bill->linked_account_name ?? '')
                );
            } else {
                // Settled cash/bank bills never owed a payable — only clear if a due CR exists.
                $existingCr = round((float) FinanceAccountLedgerEntry::query()
                    ->where('finance_account_id', $payableAccount->id)
                    ->where('voucher_no', $voucherPrefix)
                    ->where('cr_amount', '>', 0)
                    ->sum('cr_amount'), 2);
                if ($existingCr <= 0.005) {
                    continue;
                }
                // Full settlement should clear the original liability, not just paid_amount.
                $paidAmount = max($paidAmount, $amount);
            }

            if ($paidAmount <= 0) {
                continue;
            }

            $this->removeRedundantExpensePayablePaidBackfill(
                (int) $payableAccount->id,
                $voucherPrefix,
                $paidAmount
            );

            $existingClearing = round((float) FinanceAccountLedgerEntry::query()
                ->where('finance_account_id', $payableAccount->id)
                ->where('dr_amount', '>', 0)
                ->where(function ($query) use ($voucherPrefix) {
                    $query
                        ->where('voucher_no', 'like', $voucherPrefix.'-P%')
                        ->orWhere('voucher_no', $voucherPrefix.'-PAID')
                        ->orWhere('voucher_no', 'like', $voucherPrefix.'-PAID%');
                })
                ->sum('dr_amount'), 2);

            $shortfall = round($paidAmount - $existingClearing, 2);
            if ($shortfall <= 0.005) {
                continue;
            }

            $this->recordExpensePayableEntry(
                $head,
                $shortfall,
                'dr',
                $entryDate,
                $voucherPrefix.'-PAID',
                null,
                trim((string) ($bill->particular ?? '')) ?: 'Partial payable payment',
                'Partial',
                (string) ($bill->remarks ?? $bill->approval_remarks ?? ''),
                (string) ($bill->client_name ?? $bill->linked_account_name ?? '')
            );
        }

        $this->syncExpensePayableBalancesFromLedger();
    }

    /**
     * Backfill {Asset} Payable CR/DR for approved asset-purchase due bills.
     * Previously skipped when a vendor account was linked.
     */
    public function backfillMissingAssetPurchasePayableEntries(): void
    {
        $bills = FinanceBillEntry::query()
            ->with('assetAccount')
            ->where('status', 'approved')
            ->where('entry_type', 'asset_purchase')
            ->where(function ($query) {
                $query
                    ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
                    ->orWhereRaw('COALESCE(paid_amount, 0) > 0');
            })
            ->orderBy('approved_at')
            ->orderBy('id')
            ->get();

        foreach ($bills as $bill) {
            $assetAccount = $bill->assetAccount;
            if (!$assetAccount || (string) $assetAccount->category !== 'asset') {
                continue;
            }

            $amount = round((float) ($bill->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $voucherNo = trim((string) ($bill->voucher_no ?? $bill->reference_no ?? ''));
            $voucherPrefix = $voucherNo !== '' ? $voucherNo : 'BILL-DUE-'.$bill->id;
            $entryDate = $bill->approved_at?->format('Y-m-d')
                ?: ($bill->payment_date?->format('Y-m-d') ?? now()->toDateString());
            $isStillDue = strtolower(trim((string) ($bill->payment_method ?? ''))) === 'due';
            $paidAmount = round((float) ($bill->paid_amount ?? 0), 2);
            if (!$isStillDue && $paidAmount <= 0) {
                continue;
            }

            $payableAccount = $this->ensureAssetPurchasePayableAccount($assetAccount);

            if ($isStillDue) {
                $this->recordAssetPurchasePayableEntry(
                    $assetAccount,
                    $amount,
                    'cr',
                    $entryDate,
                    $voucherPrefix,
                    null,
                    trim((string) ($bill->particular ?? '')) ?: 'Due payable',
                    'Due',
                    (string) ($bill->remarks ?? $bill->approval_remarks ?? ''),
                    (string) ($bill->client_name ?? $bill->linked_account_name ?? ''),
                    (int) $bill->id
                );
            } else {
                $existingCr = round((float) FinanceAccountLedgerEntry::query()
                    ->where('finance_account_id', $payableAccount->id)
                    ->where('voucher_no', $voucherPrefix)
                    ->where('cr_amount', '>', 0)
                    ->sum('cr_amount'), 2);
                if ($existingCr <= 0.005) {
                    continue;
                }
                $paidAmount = max($paidAmount, $amount);
            }

            if ($paidAmount <= 0) {
                continue;
            }

            $existingClearing = round((float) FinanceAccountLedgerEntry::query()
                ->where('finance_account_id', $payableAccount->id)
                ->where('dr_amount', '>', 0)
                ->where(function ($query) use ($voucherPrefix) {
                    $query
                        ->where('voucher_no', 'like', $voucherPrefix.'-P%')
                        ->orWhere('voucher_no', $voucherPrefix.'-PAID')
                        ->orWhere('voucher_no', 'like', $voucherPrefix.'-PAID%');
                })
                ->sum('dr_amount'), 2);

            $shortfall = round($paidAmount - $existingClearing, 2);
            if ($shortfall <= 0.005) {
                continue;
            }

            $this->recordAssetPurchasePayableEntry(
                $assetAccount,
                $shortfall,
                'dr',
                $entryDate,
                $voucherPrefix.'-PAID',
                null,
                trim((string) ($bill->particular ?? '')) ?: 'Partial payable payment',
                'Partial',
                (string) ($bill->remarks ?? $bill->approval_remarks ?? ''),
                (string) ($bill->client_name ?? $bill->linked_account_name ?? ''),
                (int) $bill->id
            );
        }
    }

    /**
     * Drop legacy `{voucher}-PAID` backfill rows when live `{voucher}-P{txn}`
     * settlements already cover paid_amount (prevents TB payable going to zero early).
     */
    private function removeRedundantExpensePayablePaidBackfill(
        int $payableAccountId,
        string $voucherPrefix,
        float $paidAmount
    ): void {
        $liveClearing = round((float) FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $payableAccountId)
            ->where('dr_amount', '>', 0)
            ->where('voucher_no', 'like', $voucherPrefix.'-P%')
            ->where('voucher_no', 'not like', $voucherPrefix.'-PAID%')
            ->sum('dr_amount'), 2);

        if ($liveClearing + 0.005 < $paidAmount) {
            return;
        }

        $backfillRows = FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $payableAccountId)
            ->where('dr_amount', '>', 0)
            ->where(function ($query) use ($voucherPrefix) {
                $query
                    ->where('voucher_no', $voucherPrefix.'-PAID')
                    ->orWhere('voucher_no', 'like', $voucherPrefix.'-PAID%');
            })
            ->get();

        if ($backfillRows->isEmpty()) {
            return;
        }

        $removed = 0.0;
        foreach ($backfillRows as $row) {
            $removed = round($removed + (float) $row->dr_amount, 2);
            $row->delete();
        }

        if ($removed > 0) {
            $account = $this->model->query()->find($payableAccountId);
            if ($account) {
                // Removing a DR increases liability balance again.
                $account->balance = round((float) $account->balance + $removed, 2);
                $account->save();
            }
        }
    }

    private function syncExpensePayableBalancesFromLedger(): void
    {
        $accounts = $this->model->query()
            ->where('category', self::EXPENSE_PAYABLE_CATEGORY)
            ->get();

        foreach ($accounts as $account) {
            $totals = FinanceAccountLedgerEntry::query()
                ->where('finance_account_id', $account->id)
                ->selectRaw('COALESCE(SUM(dr_amount), 0) as total_dr, COALESCE(SUM(cr_amount), 0) as total_cr')
                ->first();

            // Liability wallet: CR − DR
            $account->balance = round(
                (float) ($totals->total_cr ?? 0) - (float) ($totals->total_dr ?? 0),
                2
            );
            $account->save();
        }
    }

    /**
     * Backfill Bills Receivable from historical Sale due collections / settlements.
     */
    private function backfillMissingBillsReceivableEntries(): void
    {
        $dueRows = FinanceSaleCollection::query()
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->orderBy('collection_date')
            ->orderBy('id')
            ->get();

        foreach ($dueRows as $row) {
            $amount = round((float) ($row->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $this->recordBillsReceivableEntry(
                $amount,
                'dr',
                (string) ($row->collection_date ?? now()->toDateString()),
                trim((string) ($row->voucher_no ?? $row->entry_no ?? '')),
                (int) ($row->finance_account_type_transaction_id ?? 0) ?: null,
                'Bills Receivable — Due',
                'Due',
                (string) ($row->remarks ?? ''),
                trim((string) ($row->candidate_name ?? '')),
                '',
                (string) ($row->job_title ?? '')
            );
        }

        $settleRows = FinanceSaleCollection::query()
            ->where(function ($query) {
                foreach (['cash', 'bank', 'balance', 'expense_link'] as $method) {
                    $query->orWhereRaw('LOWER(COALESCE(payment_method, "")) = ?', [$method]);
                }
            })
            ->orderBy('collection_date')
            ->orderBy('id')
            ->get();

        foreach ($settleRows as $row) {
            $applicationId = (int) ($row->application_id ?? 0);
            if ($applicationId <= 0) {
                continue;
            }

            $hadDue = FinanceSaleCollection::query()
                ->where('application_id', $applicationId)
                ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
                ->where('id', '<', (int) $row->id)
                ->exists();

            if (!$hadDue) {
                continue;
            }

            $amount = round((float) ($row->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $methodLabel = match (strtolower((string) ($row->payment_method ?? ''))) {
                'cash' => 'Cash',
                'bank' => 'Bank',
                'balance' => 'Adjust from Balance',
                'expense_link' => 'Expense Link',
                default => ucfirst((string) ($row->payment_method ?? '')),
            };

            $this->recordBillsReceivableEntry(
                $amount,
                'cr',
                (string) ($row->collection_date ?? now()->toDateString()),
                trim((string) ($row->voucher_no ?? $row->entry_no ?? '')),
                (int) ($row->finance_account_type_transaction_id ?? 0) ?: null,
                'Bills Receivable — Received',
                $methodLabel,
                (string) ($row->remarks ?? ''),
                trim((string) ($row->candidate_name ?? '')),
                '',
                (string) ($row->job_title ?? '')
            );
        }

        $this->syncBillsReceivableBalanceFromLedger();
    }

    /**
     * Keep consolidating Sale Receivable wallet balance aligned with ledger DR − CR.
     */
    private function syncBillsReceivableBalanceFromLedger(): void
    {
        $account = $this->model->query()
            ->where('category', self::BILLS_RECEIVABLE_CATEGORY)
            ->where('code', self::BILLS_RECEIVABLE_ACCOUNT_CODE)
            ->first();

        if (!$account) {
            return;
        }

        $totals = FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $account->id)
            ->selectRaw('COALESCE(SUM(dr_amount), 0) as total_dr, COALESCE(SUM(cr_amount), 0) as total_cr')
            ->first();

        $account->balance = round(
            (float) ($totals->total_dr ?? 0) - (float) ($totals->total_cr ?? 0),
            2
        );
        $account->save();
    }

    /**
     * Backfill income receivable DR/CR from historical income due / settle collections.
     */
    public function backfillMissingIncomeReceivableEntries(): void
    {
        $dueRows = FinanceIncomeCollection::query()
            ->with('incomeHead')
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->orderBy('collection_date')
            ->orderBy('id')
            ->get();

        foreach ($dueRows as $row) {
            $head = $row->incomeHead;
            if (!$head) {
                continue;
            }
            $amount = round((float) ($row->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $this->recordIncomeReceivableEntry(
                $head,
                $amount,
                'dr',
                (string) ($row->collection_date ?? now()->toDateString()),
                trim((string) ($row->voucher_no ?? $row->reference_no ?? '')),
                (int) ($row->finance_account_type_transaction_id ?? 0) ?: null,
                trim((string) ($row->particular ?? '')) ?: 'Due receivable',
                'Due',
                (string) ($row->remarks ?? ''),
                (string) ($row->linked_account_name ?? $row->client_name ?? '')
            );
        }

        $settleRows = FinanceIncomeCollection::query()
            ->with('incomeHead')
            ->where(function ($query) {
                foreach (['cash', 'bank', 'expense_link'] as $method) {
                    $query->orWhereRaw('LOWER(COALESCE(payment_method, "")) = ?', [$method]);
                }
            })
            ->where(function ($query) {
                $query
                    ->whereNotNull('settles_income_collection_id')
                    ->orWhereNotNull('candidates');
            })
            ->orderBy('collection_date')
            ->orderBy('id')
            ->get();

        foreach ($settleRows as $row) {
            $head = $row->incomeHead;
            if (!$head) {
                continue;
            }

            $settlesId = (int) ($row->settles_income_collection_id ?? 0);
            $shouldSettle = $settlesId > 0;
            if (!$shouldSettle && is_array($row->candidates) && $row->candidates !== []) {
                // Candidate income cash after a due for same head/apps.
                $shouldSettle = true;
                foreach ($row->candidates as $candidate) {
                    if (!is_array($candidate)) {
                        continue;
                    }
                    $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
                    if ($applicationId <= 0) {
                        $shouldSettle = false;
                        break;
                    }
                    $hadDue = FinanceIncomeCollection::query()
                        ->where('income_head_id', $head->id)
                        ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
                        ->where('id', '<', (int) $row->id)
                        ->whereNotNull('candidates')
                        ->get(['candidates'])
                        ->contains(function ($dueRow) use ($applicationId) {
                            foreach ((array) $dueRow->candidates as $dueCandidate) {
                                if (!is_array($dueCandidate)) {
                                    continue;
                                }
                                $dueAppId = (int) ($dueCandidate['application_id'] ?? $dueCandidate['candidate_id'] ?? 0);
                                if ($dueAppId === $applicationId) {
                                    return true;
                                }
                            }

                            return false;
                        });
                    if (!$hadDue) {
                        $shouldSettle = false;
                        break;
                    }
                }
            }

            if (!$shouldSettle) {
                continue;
            }

            $amount = round((float) ($row->amount ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $methodLabel = match (strtolower((string) ($row->payment_method ?? ''))) {
                'cash' => 'Cash',
                'bank' => 'Bank',
                'expense_link' => 'Expense Link',
                default => ucfirst((string) ($row->payment_method ?? '')),
            };

            $this->recordIncomeReceivableEntry(
                $head,
                $amount,
                'cr',
                (string) ($row->collection_date ?? now()->toDateString()),
                trim((string) ($row->voucher_no ?? $row->reference_no ?? '')),
                (int) ($row->finance_account_type_transaction_id ?? 0) ?: null,
                trim((string) ($row->particular ?? '')) ?: 'Receivable settled',
                $methodLabel,
                (string) ($row->remarks ?? ''),
                (string) ($row->linked_account_name ?? $row->client_name ?? '')
            );
        }
    }

    /**
     * Credit Sale ledger for recognized sale income (due raise or cash/bank/balance/expense_link).
     */
    public function recordSaleIncomeEntry(
        float $amount,
        string $entryDate,
        string $voucherNo,
        ?int $typeTransactionId = null,
        string $particular = 'Sale',
        string $paymentMethod = '',
        string $remarks = '',
        string $clientName = '',
        string $demandLetter = '',
        string $job = '',
    ): void {
        if ($amount <= 0) {
            return;
        }

        $saleAccount = $this->ensureSaleAccount();
        $voucherNo = trim($voucherNo);
        $particular = trim($particular) !== '' ? trim($particular) : 'Sale';

        $existingEntry = null;
        if ($typeTransactionId || $voucherNo !== '') {
            $existingEntry = FinanceAccountLedgerEntry::query()
                ->where('finance_account_id', $saleAccount->id)
                ->where(function ($query) use ($typeTransactionId, $voucherNo) {
                    if ($typeTransactionId) {
                        $query->where('finance_account_type_transaction_id', $typeTransactionId);
                    }
                    if ($voucherNo !== '') {
                        $query->orWhere('voucher_no', $voucherNo);
                    }
                })
                ->first();
        }

        if ($existingEntry) {
            if ($typeTransactionId && !(int) $existingEntry->finance_account_type_transaction_id) {
                $existingEntry->update([
                    'finance_account_type_transaction_id' => $typeTransactionId,
                ]);
            }

            return;
        }

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $saleAccount->id,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
            'demand_letter' => $demandLetter !== '' ? $demandLetter : null,
            'job' => $job !== '' ? $job : null,
            'client_name' => $clientName !== '' ? $clientName : null,
            'dr_amount' => 0,
            'discount' => 0,
            'cr_amount' => $amount,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks !== '' ? $remarks : null,
        ]);

        $saleAccount->balance = round((float) $saleAccount->balance + $amount, 2);
        $saleAccount->save();
    }

    /**
     * Backfill Sale ledger CRs from historical sale collections.
     */
    private function backfillMissingSaleEntries(): void
    {
        $this->backfillAgentClientSaleEntries();
        $this->backfillCandidateSaleEntries();
    }

    /**
     * Agent/client accrual: credit Sale on due raise, or on cash/bank/balance/expense_link
     * when there is no prior due for that application (avoids double-count on settle).
     */
    private function backfillAgentClientSaleEntries(): void
    {
        $cashMethods = ['cash', 'bank', 'balance', 'expense_link'];

        $collections = FinanceSaleCollection::query()
            ->where(function ($query) {
                $query
                    ->whereNull('payer_type')
                    ->orWhereRaw('LOWER(COALESCE(payer_type, "")) != ?', ['candidate']);
            })
            ->orderBy('collection_date')
            ->orderBy('id')
            ->get();

        $appsWithDue = [];
        $appsWithCash = [];
        foreach ($collections as $row) {
            $applicationId = (int) ($row->application_id ?? 0);
            if ($applicationId <= 0) {
                continue;
            }

            $method = strtolower(trim((string) ($row->payment_method ?? '')));
            if ($method === 'due') {
                $appsWithDue[$applicationId] = true;
            } elseif (in_array($method, $cashMethods, true)) {
                $appsWithCash[$applicationId] = true;
            }
        }

        $grouped = [];
        foreach ($collections as $row) {
            $method = strtolower(trim((string) ($row->payment_method ?? '')));
            $applicationId = (int) ($row->application_id ?? 0);

            if (in_array($method, $cashMethods, true)) {
                // Settling a prior due: Sale was (or will be) recognized on the due row.
                if ($applicationId > 0 && isset($appsWithDue[$applicationId])) {
                    continue;
                }
            } elseif ($method === 'due') {
                // Historical cash-basis already credited Sale on settle — skip due.
                if ($applicationId > 0 && isset($appsWithCash[$applicationId])) {
                    continue;
                }
            } else {
                continue;
            }

            $typeTxnId = (int) ($row->finance_account_type_transaction_id ?? 0);
            $voucherNo = trim((string) ($row->voucher_no ?? $row->entry_no ?? ''));
            $groupKey = $typeTxnId > 0
                ? "txn:{$typeTxnId}"
                : ($voucherNo !== '' ? "voucher:{$voucherNo}" : "row:{$row->id}");

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'amount' => 0.0,
                    'entry_date' => (string) ($row->collection_date ?? now()->toDateString()),
                    'voucher_no' => $voucherNo,
                    'type_transaction_id' => $typeTxnId > 0 ? $typeTxnId : null,
                    'payment_method' => (string) ($row->payment_method ?? ''),
                    'remarks' => (string) ($row->remarks ?? ''),
                    'job' => (string) ($row->job_title ?? ''),
                ];
            }

            $grouped[$groupKey]['amount'] = round(
                $grouped[$groupKey]['amount'] + (float) $row->amount,
                2
            );
        }

        foreach ($grouped as $group) {
            if ($group['amount'] <= 0) {
                continue;
            }

            $methodLabel = match (strtolower($group['payment_method'])) {
                'cash' => 'Cash',
                'bank' => 'Bank',
                'due' => 'Due',
                'balance' => 'Adjust from Balance',
                'expense_link' => 'Expense Link',
                default => ucfirst((string) $group['payment_method']),
            };

            $this->recordSaleIncomeEntry(
                $group['amount'],
                $group['entry_date'],
                $group['voucher_no'],
                $group['type_transaction_id'],
                'Sale',
                $methodLabel,
                $group['remarks'],
                '',
                '',
                $group['job']
            );
        }
    }

    /**
     * Candidate: recognize Sale once per application when first billed (sale_price).
     */
    private function backfillCandidateSaleEntries(): void
    {
        $collections = FinanceSaleCollection::query()
            ->whereRaw('LOWER(COALESCE(payer_type, "")) = ?', ['candidate'])
            ->orderBy('collection_date')
            ->orderBy('id')
            ->get();

        $byApplication = [];
        foreach ($collections as $row) {
            $applicationId = (int) ($row->application_id ?? 0);
            if ($applicationId <= 0 || isset($byApplication[$applicationId])) {
                continue;
            }

            $salePrice = round((float) ($row->sale_price ?? 0), 2);
            if ($salePrice <= 0) {
                $salePrice = round((float) ($row->amount ?? 0), 2);
            }
            if ($salePrice <= 0) {
                continue;
            }

            $byApplication[$applicationId] = [
                'amount' => $salePrice,
                'entry_date' => (string) ($row->collection_date ?? now()->toDateString()),
                'voucher_no' => trim((string) ($row->voucher_no ?? $row->entry_no ?? '')),
                'type_transaction_id' => (int) ($row->finance_account_type_transaction_id ?? 0) ?: null,
                'payment_method' => (string) ($row->payment_method ?? ''),
                'remarks' => (string) ($row->remarks ?? ''),
                'job' => (string) ($row->job_title ?? ''),
            ];
        }

        // Group same voucher/txn so multi-candidate bills post one Sale CR.
        $grouped = [];
        foreach ($byApplication as $applicationId => $item) {
            $typeTxnId = (int) ($item['type_transaction_id'] ?? 0);
            $voucherNo = trim((string) ($item['voucher_no'] ?? ''));
            $groupKey = $typeTxnId > 0
                ? "txn:{$typeTxnId}"
                : ($voucherNo !== '' ? "voucher:{$voucherNo}" : "app:{$applicationId}");

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = $item;
                $grouped[$groupKey]['amount'] = 0.0;
            }

            $grouped[$groupKey]['amount'] = round(
                $grouped[$groupKey]['amount'] + $item['amount'],
                2
            );
        }

        foreach ($grouped as $group) {
            if ($group['amount'] <= 0) {
                continue;
            }

            $methodLabel = match (strtolower($group['payment_method'])) {
                'cash' => 'Cash',
                'bank' => 'Bank',
                'due' => 'Due',
                'balance' => 'Adjust from Balance',
                'expense_link' => 'Expense Link',
                default => ucfirst((string) $group['payment_method']),
            };

            $this->recordSaleIncomeEntry(
                $group['amount'],
                $group['entry_date'],
                $group['voucher_no'],
                $group['type_transaction_id'],
                'Sale',
                $methodLabel,
                $group['remarks'],
                '',
                '',
                $group['job']
            );
        }
    }

    /**
     * Ensure existing main-account openings also have Capital Ledger DR offsets.
     */
    private function backfillMissingCapitalOpeningOffsets(): void
    {
        $mainAccounts = $this->model
            ->where('category', 'main')
            ->where(function ($query) {
                $query
                    ->where('opening_balance', '>', 0)
                    ->orWhere('balance', '>', 0);
            })
            ->get();

        foreach ($mainAccounts as $mainAccount) {
            $openingAmount = (float) $mainAccount->opening_balance > 0
                ? (float) $mainAccount->opening_balance
                : (float) $mainAccount->balance;

            if ($openingAmount <= 0) {
                continue;
            }

            $hasOpeningLedger = FinanceAccountLedgerEntry::query()
                ->where('finance_account_id', $mainAccount->id)
                ->where('particular', 'Opening Balance')
                ->exists();

            if (!$hasOpeningLedger && (float) $mainAccount->opening_balance <= 0) {
                continue;
            }

            $this->recordOpeningBalanceTransaction($mainAccount, $openingAmount);
        }
    }

    /**
     * Record opening balance as an account-type transaction (shows on /finance/transactions)
     * and link the matching ledger CR entry.
     * For main accounts, also posts the offsetting DR on the Capital Ledger.
     */
    public function recordOpeningBalanceTransaction(FinanceAccount $account, ?float $amount = null): ?FinanceAccountTypeTransaction
    {
        $openingAmount = $amount !== null
            ? (float) $amount
            : ((float) $account->opening_balance > 0
                ? (float) $account->opening_balance
                : (float) $account->balance);

        if ($openingAmount <= 0) {
            return null;
        }

        $entryDate = optional($account->created_at)->toDateString() ?? now()->toDateString();
        $voucherNo = sprintf('OB-%03d/%s', $account->id, now()->format('y'));
        $accountLabel = $this->accountLabel($account);
        $particular = 'Opening Balance';
        $remarks = 'Opening balance forwarded';
        $transactionType = 'opening_balance';

        $existingTypeTxn = FinanceAccountTypeTransaction::query()
            ->where('transaction_type', $transactionType)
            ->where(function ($query) use ($account) {
                $query
                    ->where('account_id', $account->id)
                    ->orWhere('from_account_id', $account->id);
            })
            ->where('voucher_no', $voucherNo)
            ->first();

        $typeTransaction = $existingTypeTxn ?: FinanceAccountTypeTransaction::query()->create([
            'transaction_type' => $transactionType,
            'amount' => $openingAmount,
            'transaction_date' => $entryDate,
            'particular' => $particular,
            'reference_no' => null,
            'remarks' => $remarks,
            'voucher_no' => $voucherNo,
            'account_category' => $account->category,
            'main_account_type' => (string) ($account->account_type ?? ''),
            'account_id' => $account->id,
            'account_label' => $accountLabel,
        ]);

        $ledgerEntry = FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $account->id)
            ->where('particular', $particular)
            ->first();

        if ($ledgerEntry) {
            if (!(int) $ledgerEntry->finance_account_type_transaction_id) {
                $ledgerEntry->update([
                    'finance_account_type_transaction_id' => $typeTransaction->id,
                ]);
            }
        } else {
            FinanceAccountLedgerEntry::create([
                'finance_account_id' => $account->id,
                'finance_account_type_transaction_id' => $typeTransaction->id,
                'entry_date' => $entryDate,
                'particular' => $particular,
                'voucher_no' => $voucherNo,
                'dr_amount' => 0,
                'discount' => 0,
                'cr_amount' => $openingAmount,
                'payment_method' => '',
                'remarks' => $remarks,
            ]);
        }

        if ($account->category === 'main') {
            $this->recordCapitalOpeningOffset($account, $typeTransaction, $openingAmount, $entryDate, $voucherNo);
        }

        return $typeTransaction;
    }

    /**
     * Offset main-account opening CR with a Capital Ledger DR (balance = CR − DR).
     */
    private function recordCapitalOpeningOffset(
        FinanceAccount $mainAccount,
        FinanceAccountTypeTransaction $typeTransaction,
        float $openingAmount,
        string $entryDate,
        string $voucherNo,
    ): void {
        $capital = $this->ensureCapitalAccount();
        $accountLabel = $this->accountLabel($mainAccount);
        $particular = "Opening Balance — {$accountLabel}";
        $remarks = "Offset for main account opening ({$accountLabel})";

        $existingCapitalEntry = FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $capital->id)
            ->where(function ($query) use ($typeTransaction, $voucherNo) {
                $query
                    ->where('finance_account_type_transaction_id', $typeTransaction->id)
                    ->orWhere('voucher_no', $voucherNo);
            })
            ->first();

        if ($existingCapitalEntry) {
            if (!(int) $existingCapitalEntry->finance_account_type_transaction_id) {
                $existingCapitalEntry->update([
                    'finance_account_type_transaction_id' => $typeTransaction->id,
                ]);
            }

            return;
        }

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $capital->id,
            'finance_account_type_transaction_id' => $typeTransaction->id,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo,
            'client_name' => $accountLabel,
            'dr_amount' => $openingAmount,
            'discount' => 0,
            'cr_amount' => 0,
            'payment_method' => '',
            'remarks' => $remarks,
        ]);

        $capital->balance = round((float) $capital->balance - $openingAmount, 2);
        $capital->save();
    }

    private function accountLabel(FinanceAccount $account): string
    {
        $code = trim((string) ($account->code ?? ''));
        $name = trim((string) ($account->account_name ?? $account->account_label ?? ''));

        if ($name === '') {
            $name = trim((string) ($account->account_type ?? 'Account'));
        }

        return $code !== '' ? "{$name} — {$code}" : $name;
    }

    public function updateFinanceAccount(FinanceAccount $financeAccount, array $data): FinanceAccount
    {
        return $this->mutate(function () use ($financeAccount, $data) {
            if ($financeAccount->category === 'main') {
                unset($data['balance'], $data['opening_balance']);
            }

            $openingAmount = array_key_exists('opening_amount', $data)
                ? (float) $data['opening_amount']
                : null;
            $openingType = $data['opening_amount_type'] ?? null;
            unset($data['opening_amount'], $data['opening_amount_type'], $data['main_account_id']);

            $category = (string) ($financeAccount->category ?? $data['category'] ?? '');
            if (
                in_array($category, self::PARTY_ZERO_BALANCE_CATEGORIES, true)
                && $openingAmount !== null
                && $openingAmount > 0
            ) {
                throw ValidationException::withMessages([
                    'opening_amount' => [
                        'Opening amount cannot be set for agent, vendor, principal, client, or staff accounts.',
                    ],
                ]);
            }

            if (
                $openingAmount !== null
                && $openingAmount > 0
                && !in_array($category, self::PARTY_ZERO_BALANCE_CATEGORIES, true)
            ) {
                $this->setPartyOpeningAmount($financeAccount, $openingAmount, (string) $openingType);
            }

            $financeAccount->update($data);

            return $financeAccount->fresh();
        });
    }

    /**
     * Set an opening ledger amount for a party account that has no ledger yet.
     * receivable → DR, payable → CR (ledger-only; not bills receivable/payable).
     */
    public function setPartyOpeningAmount(
        FinanceAccount $account,
        float $amount,
        string $type,
    ): FinanceAccount {
        if (in_array((string) ($account->category ?? ''), self::PARTY_ZERO_BALANCE_CATEGORIES, true)) {
            throw ValidationException::withMessages([
                'opening_amount' => [
                    'Opening amount cannot be set for agent, vendor, principal, client, or staff accounts.',
                ],
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'opening_amount' => ['Opening amount must be greater than zero.'],
            ]);
        }

        if (!in_array($type, ['receivable', 'payable'], true)) {
            throw ValidationException::withMessages([
                'opening_amount_type' => ['Please choose Receivable or Payable for this amount.'],
            ]);
        }

        if ($account->ledgerEntries()->exists()) {
            throw ValidationException::withMessages([
                'opening_amount' => ['Amount can only be set when the account has no ledger entries.'],
            ]);
        }

        $isReceivable = $type === 'receivable';
        $dr = $isReceivable ? $amount : 0;
        $cr = $isReceivable ? 0 : $amount;
        $particular = $isReceivable ? 'Opening Receivable' : 'Opening Payable';
        $remarks = $isReceivable
            ? 'Opening receivable amount (ledger)'
            : 'Opening payable amount (ledger)';
        $entryDate = now()->toDateString();
        $voucherNo = sprintf('OB-%03d/%s', $account->id, now()->format('y'));
        $accountLabel = $this->accountLabel($account);

        $typeTransaction = FinanceAccountTypeTransaction::query()->create([
            'transaction_type' => 'opening_balance',
            'amount' => $amount,
            'transaction_date' => $entryDate,
            'particular' => $particular,
            'reference_no' => null,
            'remarks' => $remarks,
            'voucher_no' => $voucherNo,
            'account_category' => $account->category,
            'main_account_type' => (string) ($account->account_type ?? ''),
            'account_id' => $account->id,
            'account_label' => $accountLabel,
        ]);

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $account->id,
            'finance_account_type_transaction_id' => $typeTransaction->id,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo,
            'dr_amount' => $dr,
            'discount' => 0,
            'cr_amount' => $cr,
            'payment_method' => '',
            'remarks' => $remarks,
        ]);

        $account->opening_balance = $amount;
        // Stored balance follows ledger net (CR − DR).
        $account->balance = round($cr - $dr, 2);
        $account->save();

        return $account;
    }

    public function deleteFinanceAccount(FinanceAccount $financeAccount): bool
    {
        return $this->mutate(function () use ($financeAccount) {
            $this->assertAccountCanBeDeleted($financeAccount);
            $this->detachRelatedRecords($financeAccount);

            return (bool) $financeAccount->delete();
        });
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(function () use ($ids) {
            $accounts = $this->model->whereIn('id', $ids)->get();

            foreach ($accounts as $account) {
                $this->assertAccountCanBeDeleted($account);
                $this->detachRelatedRecords($account);
            }

            return $this->model->whereIn('id', $ids)->delete();
        });
    }

    private function assertAccountCanBeDeleted(FinanceAccount $financeAccount): void
    {
        if ($financeAccount->category === self::CAPITAL_CATEGORY) {
            throw ValidationException::withMessages([
                'category' => ['Capital Ledger cannot be deleted.'],
            ]);
        }

        if ($financeAccount->category === self::SALE_CATEGORY) {
            throw ValidationException::withMessages([
                'category' => ['Sale Ledger cannot be deleted.'],
            ]);
        }

        if ($financeAccount->category === self::BILLS_RECEIVABLE_CATEGORY) {
            throw ValidationException::withMessages([
                'category' => ['Bills Receivable Ledger cannot be deleted.'],
            ]);
        }

        if ($financeAccount->category === self::INCOME_RECEIVABLE_CATEGORY) {
            throw ValidationException::withMessages([
                'category' => ['Income Receivable ledger cannot be deleted.'],
            ]);
        }

        if ($financeAccount->category === self::EXPENSE_PAYABLE_CATEGORY) {
            throw ValidationException::withMessages([
                'category' => ['Expense Payable ledger cannot be deleted.'],
            ]);
        }

        if ($financeAccount->category === 'main' && (float) $financeAccount->balance > 0) {
            throw ValidationException::withMessages([
                'balance' => ['Main accounts with balance greater than 0 cannot be deleted.'],
            ]);
        }
    }

    private function detachRelatedRecords(FinanceAccount $financeAccount): void
    {
        $accountId = (int) $financeAccount->id;

        // Transfer/deposit/withdraw history blocks delete via FK; remove those links first.
        // Counterparty ledger rows stay and get transaction_id nulled (nullOnDelete).
        FinanceAccountTransaction::query()
            ->where(function ($query) use ($accountId) {
                $query
                    ->where('from_account_id', $accountId)
                    ->orWhere('to_account_id', $accountId);
            })
            ->delete();
    }

    /**
     * Create (or sync) a finance applicant account when a direct candidate
     * application has candidate payment responsibility.
     */
    public function ensureApplicantAccount(
        Application $application,
        ?string $appliedThrough = null,
        mixed $paymentResponsibility = null,
    ): ?FinanceAccount {
        $appliedThrough ??= $application->applied_through;
        $paymentResponsibility ??= $application->payment_responsibility;

        if (!$this->shouldHaveApplicantAccountValues($appliedThrough, $paymentResponsibility)) {
            return null;
        }

        $accountName = ApplicationPresenter::fullName(
            $application->given_name,
            $application->sur_name
        ) ?: ('Applicant #' . $application->id);

        return $this->mutate(function () use ($application, $accountName) {
            $account = $this->model->firstOrNew([
                'category' => self::APPLICANT_CATEGORY,
                'entity_id' => $application->id,
            ]);

            $account->account_name = $accountName;
            $account->code = $application->application_id;
            $account->phone = $application->mobile;
            $account->metadata = array_merge($account->metadata ?? [], [
                'passport_no' => $application->passport_no,
            ]);

            if (!$account->exists) {
                $account->balance = 0;
                $account->opening_balance = 0;
                $account->status = 'active';
            }

            $account->save();

            return $account;
        });
    }

    public function shouldHaveApplicantAccount(Application $application): bool
    {
        return $this->shouldHaveApplicantAccountValues(
            $application->applied_through,
            $application->payment_responsibility
        );
    }

    public function shouldHaveApplicantAccountValues(
        mixed $appliedThrough,
        mixed $paymentResponsibility,
    ): bool {
        if ((string) ($appliedThrough ?? '') !== 'direct_candidate') {
            return false;
        }

        return JobListPayerHelper::has(
            $paymentResponsibility,
            JobListPayerHelper::CANDIDATE
        );
    }

    /**
     * Create (or sync) the finance account for an expense head.
     */
    public function ensureExpenseHeadAccount(ExpenseHead $expenseHead): ?FinanceAccount
    {
        $expenseHead->loadMissing('expenseCategory');
        $categoryCode = $expenseHead->expenseCategory?->code;
        $accountCategory = $categoryCode
            ? config("finance_accounts.expense_category_code_to_account_category.{$categoryCode}")
            : null;

        if (!$accountCategory) {
            return null;
        }

        return $this->mutate(function () use ($expenseHead, $accountCategory) {
            $account = $this->model->firstOrNew([
                'category' => $accountCategory,
                'expense_head_id' => $expenseHead->id,
            ]);

            $account->account_name = $expenseHead->name;
            $account->expense_category_id = $expenseHead->expense_category_id;
            $account->base_price = $expenseHead->base_price ?? 0;

            if (!$account->exists) {
                $account->balance = 0;
                $account->opening_balance = 0;
                $account->status = $this->normalizeAccountStatus($expenseHead->status);
            }

            $account->save();

            return $account;
        });
    }

    /**
     * Create (or sync) the finance account for an income head.
     */
    public function ensureIncomeHeadAccount(IncomeHead $incomeHead): ?FinanceAccount
    {
        $incomeHead->loadMissing('incomeCategory');
        $categoryCode = $incomeHead->incomeCategory?->code;
        $accountCategory = $categoryCode
            ? config("finance_accounts.income_category_code_to_account_category.{$categoryCode}")
            : null;

        if (!$accountCategory) {
            return null;
        }

        return $this->mutate(function () use ($incomeHead, $accountCategory) {
            $account = $this->model->firstOrNew([
                'category' => $accountCategory,
                'income_head_id' => $incomeHead->id,
            ]);

            $account->account_name = $incomeHead->name;
            $account->income_category_id = $incomeHead->income_category_id;
            $account->base_price = $incomeHead->base_price ?? 0;

            if (!$account->exists) {
                $account->balance = 0;
                $account->opening_balance = 0;
                $account->status = $this->normalizeAccountStatus($incomeHead->status);
            }

            $account->save();

            return $account;
        });
    }

    /**
     * Create (or sync) a finance vendor account when a vendor master is created/updated.
     */
    public function ensureVendorAccount(Vendor $vendor): FinanceAccount
    {
        $vendor->loadMissing('user');

        return $this->mutate(function () use ($vendor) {
            $account = $this->model->firstOrNew([
                'category' => 'vendor',
                'entity_id' => $vendor->id,
            ]);

            $account->account_name = $vendor->organization_name;
            $account->code = $vendor->vendor_id;
            $account->phone = $vendor->user?->phone;
            $account->metadata = array_merge($account->metadata ?? [], [
                'vendor_type' => $vendor->vendor_type,
                'contact_person' => $vendor->contact_person,
            ]);

            if (!$account->exists) {
                $account->balance = 0;
                $account->opening_balance = 0;
            }

            $account->status = ((int) ($vendor->user?->status ?? 1) === 1) ? 'active' : 'inactive';
            $account->save();

            return $account;
        });
    }

    private function normalizeAccountStatus(mixed $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }

    /**
     * Resolve the Operating Expense head account designated for bills receivable
     * settlement without cash/bank payment.
     */
    public function resolveBillsReceivableLinkedExpenseAccount(): FinanceAccount
    {
        $head = ExpenseHead::query()
            ->where('is_bills_receivable_link', true)
            ->where('status', 'active')
            ->first();

        if (!$head) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'No Operating Expense head is linked for bills receivable settlement. Link one under Expense Setup first.',
                ],
            ]);
        }

        $account = $this->ensureExpenseHeadAccount($head);
        if (!$account) {
            throw ValidationException::withMessages([
                'payment_method' => ['Could not resolve the linked expense head account.'],
            ]);
        }

        $locked = $this->model->newQuery()->lockForUpdate()->find($account->id);
        if (!$locked || $locked->status !== 'active') {
            throw ValidationException::withMessages([
                'payment_method' => ['Linked expense head account is missing or inactive.'],
            ]);
        }

        return $locked;
    }

    /**
     * Resolve the Operating Income head account designated for bills payable
     * settlement without cash/bank payment.
     */
    public function resolveBillsPayableLinkedIncomeAccount(): FinanceAccount
    {
        $head = IncomeHead::query()
            ->where('is_bills_payable_link', true)
            ->where('status', 'active')
            ->first();

        if (!$head) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'No Operating Income head is linked for bills payable settlement. Link one under Income Setup first.',
                ],
            ]);
        }

        $account = $this->ensureIncomeHeadAccount($head);
        if (!$account) {
            throw ValidationException::withMessages([
                'payment_method' => ['Could not resolve the linked income head account.'],
            ]);
        }

        $locked = $this->model->newQuery()->lockForUpdate()->find($account->id);
        if (!$locked || $locked->status !== 'active') {
            throw ValidationException::withMessages([
                'payment_method' => ['Linked income head account is missing or inactive.'],
            ]);
        }

        return $locked;
    }
}
