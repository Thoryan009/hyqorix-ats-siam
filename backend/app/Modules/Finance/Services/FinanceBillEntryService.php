<?php

namespace App\Modules\Finance\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Employee\Models\Employee;
use App\Modules\Finance\Models\ExpenseCategory;
use App\Modules\Finance\Models\ExpenseHead;
use App\Modules\Finance\Models\FinanceAccount;
use App\Modules\Finance\Models\FinanceAccountLedgerEntry;
use App\Modules\Finance\Models\FinanceAccountTypeTransaction;
use App\Modules\Finance\Models\FinanceBillEntry;
use App\Modules\Finance\Repositories\FinanceBillEntryRepository;
use App\Modules\JobList\Models\JobList;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Services\BaseCachedService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceBillEntryService extends BaseCachedService
{
    public function __construct(
        protected FinanceBillEntryRepository $repository,
        private readonly FinanceAccountService $accountService,
        private readonly FinanceAccountTypeTransactionService $typeTransactionService,
    ) {
        parent::__construct(new FinanceBillEntry());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getBillEntry(FinanceBillEntry $billEntry): FinanceBillEntry
    {
        return $this->remember(
            $this->byIdCacheKey($billEntry->id),
            fn () => $billEntry->load(['expenseCategory', 'expenseHead'])
        );
    }

    public function createBillEntry(array $data): FinanceBillEntry
    {
        return $this->mutate(function () use ($data) {
            $payload = $this->buildCreatePayload($data);

            if (empty($payload['request_no'])) {
                $payload['request_no'] = $this->nextRequestNo(Carbon::parse($payload['payment_date']));
            }

            return FinanceBillEntry::query()->create($payload);
        });
    }

    /**
     * @return FinanceBillEntry[]
     */
    public function createBillEntryBatch(array $data): array
    {
        return $this->mutate(function () use ($data) {
            $applicationIds = collect($data['application_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($applicationIds)) {
                throw ValidationException::withMessages([
                    'application_ids' => ['Please select at least one candidate application.'],
                ]);
            }

            $basePayload = $this->buildCreatePayload($data, false);
            $batchRef = trim((string) ($data['batch_ref'] ?? $data['reference_no'] ?? ''));
            if ($batchRef === '') {
                $batchRef = 'BATCH-' . str_pad((string) (FinanceBillEntry::max('id') + 1), 4, '0', STR_PAD_LEFT);
            }

            $paymentDate = $basePayload['payment_date'];
            $requestNo = trim((string) ($data['request_no'] ?? ''));
            if ($requestNo === '') {
                $requestNo = $this->nextRequestNo(Carbon::parse($paymentDate));
            }

            $entries = [];

            $this->assertNoDuplicateApplicationBills(
                $applicationIds,
                (int) $basePayload['expense_head_id']
            );

            DB::transaction(function () use ($applicationIds, $basePayload, $batchRef, $data, $requestNo, &$entries) {
                foreach ($applicationIds as $applicationId) {
                    $applicationMeta = $this->resolveApplicationMeta($applicationId);
                    $paymentDate = $basePayload['payment_date'];

                    $particular = trim((string) ($data['particular'] ?? ''));
                    if ($particular === '') {
                        $category = ExpenseCategory::find($basePayload['expense_category_id']);
                        $head = ExpenseHead::find($basePayload['expense_head_id']);
                        $particular = sprintf(
                            '%s - %s - %s (%s)',
                            $category?->name ?? 'Expense',
                            $head?->name ?? 'Head',
                            $applicationMeta['candidate_name'],
                            $applicationMeta['passport_no']
                        );
                    }

                    $entries[] = FinanceBillEntry::query()->create([
                        ...$basePayload,
                        ...$applicationMeta,
                        'particular' => $particular,
                        'batch_ref' => $batchRef,
                        'request_no' => $requestNo,
                        'voucher_no' => $this->nextVoucherNo('BILL', Carbon::parse($paymentDate)),
                    ]);
                }
            });

            return collect($entries)->map(fn (FinanceBillEntry $entry) => $entry->load(['expenseCategory', 'expenseHead']))->all();
        });
    }

    /**
     * Multiple expense-head lines, each with its own voucher/bill number.
     * Lines share batch_ref so they stay linked as one submission.
     *
     * @return FinanceBillEntry[]
     */
    public function createMultiHeadBillEntries(array $data): array
    {
        return $this->mutate(function () use ($data) {
            $lines = collect($data['lines'] ?? [])
                ->filter(fn ($line) => !empty($line['head_id']) && (float) ($line['amount'] ?? 0) > 0)
                ->values()
                ->all();

            if (empty($lines)) {
                throw ValidationException::withMessages([
                    'lines' => ['Please add at least one expense head with amount.'],
                ]);
            }

            $categoryId = (int) ($data['category_id'] ?? 0);
            $category = ExpenseCategory::find($categoryId);
            if (!$category) {
                throw ValidationException::withMessages([
                    'category_id' => ['Selected expense category is invalid.'],
                ]);
            }

            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $paymentCarbon = Carbon::parse($paymentDate);

            $batchRef = trim((string) ($data['batch_ref'] ?? $data['reference_no'] ?? ''));
            if ($batchRef === '') {
                $batchRef = $this->nextVoucherNo('BATCH', $paymentCarbon);
            }

            $requestNo = trim((string) ($data['request_no'] ?? ''));
            if ($requestNo === '') {
                $requestNo = $this->nextRequestNo($paymentCarbon);
            }

            $entries = [];

            DB::transaction(function () use (
                $lines,
                $data,
                $category,
                $categoryId,
                $paymentDate,
                $batchRef,
                $requestNo,
                &$entries
            ) {
                foreach ($lines as $line) {
                    $lineBillNo = trim((string) (
                        $line['voucher_no']
                            ?? $line['bill_no']
                            ?? $line['reference_no']
                            ?? ''
                    ));
                    $lineBillNo = $lineBillNo !== '' ? $lineBillNo : null;

                    $linePayload = [
                        ...$data,
                        'category_id' => $categoryId,
                        'head_id' => (int) $line['head_id'],
                        'amount' => (float) $line['amount'],
                        'payment_date' => $paymentDate,
                        'reference_no' => $lineBillNo ?? '',
                        'batch_ref' => $batchRef,
                        'particular' => trim((string) ($line['particular'] ?? '')),
                        'linked_account_category' => $line['linked_account_category'] ?? null,
                        'linked_account_id' => $line['linked_account_id'] ?? null,
                        'linked_account_name' => $line['linked_account_name'] ?? null,
                        'linked_account_type' => $line['linked_account_type'] ?? null,
                        'expense_cost_type' => $line['expense_cost_type'] ?? null,
                        'expense_cost_account_id' => $line['expense_cost_account_id'] ?? null,
                        'expense_cost_account_name' => $line['expense_cost_account_name'] ?? null,
                        'expense_cost_category_name' => $line['expense_cost_category_name'] ?? null,
                    ];

                    $payload = $this->buildCreatePayload($linePayload, false);

                    if (trim((string) ($payload['particular'] ?? '')) === '') {
                        $head = ExpenseHead::find((int) $line['head_id']);
                        $payload['particular'] = sprintf('%s - %s', $category->name, $head?->name ?? 'Head');
                    }

                    $payload['voucher_no'] = $lineBillNo;
                    $payload['reference_no'] = $lineBillNo;
                    $payload['batch_ref'] = $batchRef;
                    $payload['request_no'] = $requestNo;

                    $entries[] = FinanceBillEntry::query()->create($payload);
                }
            });

            return collect($entries)->map(fn (FinanceBillEntry $entry) => $entry->load(['expenseCategory', 'expenseHead']))->all();
        });
    }

    public function updateBillEntry(FinanceBillEntry $billEntry, array $data): FinanceBillEntry
    {
        if ($billEntry->status === 'approved') {
            throw ValidationException::withMessages([
                'status' => ['Approved bills cannot be edited.'],
            ]);
        }

        return $this->mutate(function () use ($billEntry, $data) {
            $billEntry->update([
                'amount' => (float) $data['amount'],
                'particular' => trim((string) ($data['particular'] ?? $billEntry->particular)),
                'reference_no' => trim((string) ($data['reference_no'] ?? '')),
                'remarks' => trim((string) ($data['remarks'] ?? '')),
                'approval_remarks' => trim((string) ($data['approval_remarks'] ?? $billEntry->approval_remarks ?? '')),
            ]);

            return $billEntry->fresh(['expenseCategory', 'expenseHead']);
        });
    }

    public function approveBillEntry(FinanceBillEntry $billEntry, array $data): FinanceBillEntry
    {
        if ($billEntry->status === 'approved') {
            throw ValidationException::withMessages([
                'status' => ['This bill entry is already approved.'],
            ]);
        }

        if ($billEntry->status === 'submitted') {
            throw ValidationException::withMessages([
                'status' => ['This bill must be approved by a manager before payment.'],
            ]);
        }

        if ($billEntry->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Only manager-approved bills can be paid from Bills To Pay.'],
            ]);
        }

        return $this->mutate(function () use ($billEntry, $data) {
            $requestedMethod = strtolower((string) ($data['payment_method'] ?? $billEntry->payment_method ?? 'cash'));
            if (!in_array($requestedMethod, ['cash', 'bank', 'due'], true)) {
                $requestedMethod = 'cash';
            }

            return DB::transaction(function () use ($billEntry, $data, $requestedMethod) {
                $billTotal = round((float) ($data['amount'] ?? $billEntry->amount), 2);
                if ($billTotal <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => ['Please enter a valid bill amount.'],
                    ]);
                }

                // pay_amount = cash/bank portion paid now.
                // Remaining (billTotal − pay_amount) stays Due → Bills Payable.
                if (array_key_exists('pay_amount', $data) && $data['pay_amount'] !== null && $data['pay_amount'] !== '') {
                    $payAmount = round((float) $data['pay_amount'], 2);
                } elseif ($requestedMethod === 'due') {
                    $payAmount = 0.0;
                } else {
                    $payAmount = $billTotal;
                }

                if ($payAmount < 0) {
                    throw ValidationException::withMessages([
                        'pay_amount' => ['Payment amount cannot be negative.'],
                    ]);
                }

                if ($payAmount > $billTotal) {
                    throw ValidationException::withMessages([
                        'pay_amount' => ["Payment amount cannot exceed the bill amount of {$billTotal}."],
                    ]);
                }

                $dueRemaining = round($billTotal - $payAmount, 2);
                $hasDueRemaining = $dueRemaining >= 0.005;
                $hasCashPayment = $payAmount >= 0.005;

                // Any unpaid remainder stays as Due so it appears in Bills Payable.
                $storedPaymentMethod = $hasDueRemaining ? 'due' : ($hasCashPayment ? $requestedMethod : 'due');
                if ($storedPaymentMethod === 'due' && $hasCashPayment && !in_array($requestedMethod, ['cash', 'bank'], true)) {
                    $requestedMethod = 'cash';
                }

                $paymentDate = $billEntry->payment_date?->format('Y-m-d') ?? now()->toDateString();
                $particular = trim((string) ($data['particular'] ?? $billEntry->particular));
                $referenceNo = trim((string) ($data['reference_no'] ?? $billEntry->reference_no ?? ''));
                $remarks = trim((string) ($data['remarks'] ?? $billEntry->remarks ?? ''));
                $approvalRemarks = trim((string) ($data['approval_remarks'] ?? ''));
                $voucherNo = trim((string) ($data['voucher_no'] ?? ''));
                if ($voucherNo === '') {
                    $voucherNo = $billEntry->voucher_no ?: $referenceNo ?: $this->nextVoucherNo('BILL', Carbon::parse($paymentDate));
                }

                $cashMethod = in_array($requestedMethod, ['cash', 'bank'], true) ? $requestedMethod : 'cash';
                $paymentFields = $hasCashPayment
                    ? $this->resolvePaymentAccountFields($data, $cashMethod)
                    : [
                        'payment_account_category' => '',
                        'payment_account_type' => null,
                        'payment_account_id' => null,
                        'payment_account_name' => '',
                    ];
                $manualApprovalFields = $this->resolveManualApprovalFields($billEntry, $data);

                $billEntry->update([
                    'amount' => $billTotal,
                    'particular' => $particular,
                    'reference_no' => $referenceNo,
                    'voucher_no' => $voucherNo,
                    'remarks' => $remarks,
                    'approval_remarks' => $approvalRemarks,
                    'payment_method' => $storedPaymentMethod,
                    'paid_amount' => $payAmount,
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => trim((string) ($data['approved_by'] ?? 'Accountant')),
                    'rejected_at' => null,
                    ...$paymentFields,
                    ...$manualApprovalFields,
                ]);

                $isAssetPurchase = ($billEntry->entry_type ?? 'expense_bill') === 'asset_purchase';
                $assetAccount = null;
                $vendorAccount = null;
                $expenseAccount = null;

                if ($isAssetPurchase) {
                    $assetAccount = $this->resolveAssetAccountForBill($billEntry);
                    if (!$assetAccount) {
                        throw ValidationException::withMessages([
                            'asset_account_id' => ['No asset account is linked to this purchase bill.'],
                        ]);
                    }

                    $assetAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->findOrFail($assetAccount->id);
                    $this->assertActiveFinanceAccount($assetAccount);

                    $vendorAccount = $this->resolveVendorAccountForBill($billEntry);
                    if (!$vendorAccount) {
                        throw ValidationException::withMessages([
                            'vendor_account_id' => ['No vendor account is linked to this asset purchase bill.'],
                        ]);
                    }

                    $vendorAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->findOrFail($vendorAccount->id);
                    $this->assertActiveFinanceAccount($vendorAccount);

                    $billEntry->update([
                        'linked_account_id' => $assetAccount->id,
                        'linked_account_name' => $this->accountLabel($assetAccount),
                        'linked_account_category' => 'asset',
                        'linked_account_type' => 'Asset',
                    ]);
                    $billEntry->refresh();
                } else {
                    $expenseAccount = $this->resolveExpenseAccountForBill($billEntry);
                    if ($expenseAccount) {
                        $expenseAccount = FinanceAccount::query()
                            ->lockForUpdate()
                            ->findOrFail($expenseAccount->id);

                        $this->assertActiveFinanceAccount($expenseAccount);

                        // Persist expense ledger link so Bills Payable / reports can filter the bill.
                        $billEntry->update($this->buildExpenseAccountLinkFields($expenseAccount, $billEntry));
                        $billEntry->refresh();
                    }
                }

                $paymentAccount = null;
                if ($hasCashPayment) {
                    if (empty($paymentFields['payment_account_id'])) {
                        throw ValidationException::withMessages([
                            'payment_account_id' => ['Payment account is required for cash or bank bill payments.'],
                        ]);
                    }

                    $paymentAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->findOrFail((int) $paymentFields['payment_account_id']);

                    $this->assertActiveFinanceAccount($paymentAccount);

                    if ((float) $paymentAccount->balance < $payAmount) {
                        throw ValidationException::withMessages([
                            'pay_amount' => ['Insufficient balance in the selected payment account.'],
                        ]);
                    }
                }

                $ledgerAccount = $assetAccount ?? $expenseAccount;
                $typeTransaction = $this->createBillPaymentTransaction(
                    $hasCashPayment ? $payAmount : $billTotal,
                    $paymentDate,
                    $particular,
                    $referenceNo,
                    $approvalRemarks ?: $remarks,
                    $voucherNo,
                    $paymentAccount,
                    $ledgerAccount,
                    $paymentFields
                );
                $typeTransactionId = $typeTransaction?->id;

                $expenseBillParticular = $particular !== '' ? $particular : ($isAssetPurchase ? 'Asset purchase approved' : 'Bill approved');
                $paymentMethodLabel = $this->resolveBillPaymentMethodLabel(
                    $hasCashPayment ? $cashMethod : 'due',
                    $paymentFields
                );

                if ($assetAccount) {
                    // Asset ledger: DR full purchase amount.
                    $this->postBillLedgerEntry(
                        $assetAccount,
                        $billEntry->id,
                        $billTotal,
                        $paymentDate,
                        $expenseBillParticular,
                        $voucherNo,
                        $billEntry->client_name ?? '',
                        $hasDueRemaining ? null : ($hasCashPayment ? $paymentMethodLabel : null),
                        $approvalRemarks ?: $remarks ?: 'Asset purchase approved',
                        $typeTransactionId,
                        false
                    );

                    if ($vendorAccount) {
                        // Vendor ledger: DR full purchase amount (charge).
                        $this->postBillLedgerEntry(
                            $vendorAccount,
                            $billEntry->id,
                            $billTotal,
                            $paymentDate,
                            $expenseBillParticular,
                            $voucherNo,
                            $this->accountLabel($assetAccount),
                            $hasDueRemaining ? null : ($hasCashPayment ? $paymentMethodLabel : null),
                            $approvalRemarks ?: $remarks ?: 'Asset purchase approved',
                            $typeTransactionId,
                            false
                        );

                        if ($hasCashPayment) {
                            $settleVoucherNo = $voucherNo !== ''
                                ? sprintf('%s-P%s', $voucherNo, $typeTransactionId ?: now()->timestamp)
                                : sprintf('BILL-PAY-%d', $billEntry->id);
                            $this->postBillLedgerEntry(
                                $vendorAccount,
                                $billEntry->id,
                                $payAmount,
                                $paymentDate,
                                $expenseBillParticular,
                                $settleVoucherNo,
                                $this->accountLabel($assetAccount),
                                $paymentMethodLabel,
                                $approvalRemarks ?: 'Partial asset purchase payment against due',
                                $typeTransactionId,
                                true
                            );
                        }
                    } elseif ($hasDueRemaining) {
                        $this->accountService->recordAssetPurchasePayableEntry(
                            $assetAccount,
                            $billTotal,
                            'cr',
                            $paymentDate,
                            $voucherNo,
                            $typeTransactionId,
                            $expenseBillParticular !== '' ? $expenseBillParticular : 'Due payable',
                            'Due',
                            $approvalRemarks ?: $remarks ?: 'Asset purchase approved as due',
                            (string) ($billEntry->client_name ?? ''),
                            $billEntry->id
                        );

                        if ($hasCashPayment) {
                            $settleVoucherNo = $voucherNo !== ''
                                ? sprintf('%s-P%s', $voucherNo, $typeTransactionId ?: now()->timestamp)
                                : sprintf('BILL-PAY-%d', $billEntry->id);
                            $this->accountService->recordAssetPurchasePayableEntry(
                                $assetAccount,
                                $payAmount,
                                'dr',
                                $paymentDate,
                                $settleVoucherNo,
                                $typeTransactionId,
                                $expenseBillParticular,
                                $paymentMethodLabel,
                                $approvalRemarks ?: 'Partial asset purchase payment against due',
                                (string) ($billEntry->client_name ?? ''),
                                $billEntry->id
                            );
                        }
                    }
                } elseif ($expenseAccount) {
                    // Expense head ledger: always DR full bill charge.
                    $this->postBillLedgerEntry(
                        $expenseAccount,
                        $billEntry->id,
                        $billTotal,
                        $paymentDate,
                        $expenseBillParticular,
                        $voucherNo,
                        $billEntry->client_name ?? '',
                        $hasDueRemaining ? null : ($hasCashPayment ? $paymentMethodLabel : null),
                        $approvalRemarks ?: $remarks ?: 'Bill approved',
                        $typeTransactionId,
                        false
                    );

                    // Settle cash/bank portion on expense ledger (CR).
                    if ($hasCashPayment) {
                        $this->postBillLedgerEntry(
                            $expenseAccount,
                            $billEntry->id,
                            $payAmount,
                            $paymentDate,
                            $expenseBillParticular,
                            $voucherNo,
                            $billEntry->client_name ?? '',
                            $paymentMethodLabel,
                            $approvalRemarks ?: ($hasDueRemaining
                                ? 'Partial bill payment approved'
                                : 'Bill payment approved'),
                            $typeTransactionId,
                            true
                        );
                    }

                    // Due remaining (or full due): CR {Head} Payable liability.
                    if ($hasDueRemaining) {
                        $expenseHead = $this->resolveExpenseHeadForBill($billEntry, $expenseAccount);
                        if ($expenseHead) {
                            // Full liability first, then clear the paid portion (matches due → payable settle flow).
                            $this->accountService->recordExpensePayableEntry(
                                $expenseHead,
                                $billTotal,
                                'cr',
                                $paymentDate,
                                $voucherNo,
                                $typeTransactionId,
                                $expenseBillParticular !== '' ? $expenseBillParticular : 'Due payable',
                                'Due',
                                $approvalRemarks ?: $remarks ?: 'Bill approved as due',
                                (string) ($billEntry->client_name ?? '')
                            );

                            if ($hasCashPayment) {
                                $settleVoucherNo = $voucherNo !== ''
                                    ? sprintf('%s-P%s', $voucherNo, $typeTransactionId ?: now()->timestamp)
                                    : sprintf('BILL-PAY-%d', $billEntry->id);
                                $this->accountService->recordExpensePayableEntry(
                                    $expenseHead,
                                    $payAmount,
                                    'dr',
                                    $paymentDate,
                                    $settleVoucherNo,
                                    $typeTransactionId,
                                    $expenseBillParticular,
                                    $paymentMethodLabel,
                                    $approvalRemarks ?: 'Partial bill payment against due',
                                    (string) ($billEntry->client_name ?? '')
                                );
                            }
                        }
                    }

                    $this->postPartyLinkedAccountApprovalLedgers(
                        $billEntry,
                        $expenseAccount,
                        $paymentAccount,
                        $billTotal,
                        $payAmount,
                        $paymentDate,
                        $expenseBillParticular,
                        $voucherNo,
                        $hasDueRemaining,
                        $hasCashPayment,
                        $paymentMethodLabel,
                        $approvalRemarks,
                        $remarks,
                        $typeTransactionId
                    );
                }

                if ($paymentAccount) {
                    // Main cash/bank ledger: DR only (cash out) for the paid portion.
                    $this->postBillLedgerEntry(
                        $paymentAccount,
                        $billEntry->id,
                        $payAmount,
                        $paymentDate,
                        $particular,
                        $voucherNo,
                        $billEntry->linked_account_name ?? $billEntry->expense_cost_account_name ?? '',
                        $paymentMethodLabel ?? ucfirst($cashMethod),
                        $approvalRemarks ?: ($hasDueRemaining
                            ? 'Partial bill payment approved'
                            : 'Bill payment approved'),
                        $typeTransactionId,
                        false
                    );

                    $advanceAssetAccount = $this->postAdvanceAdjustmentAssetLedgerIfRequired(
                        $billEntry,
                        $data,
                        (string) ($paymentFields['payment_account_category'] ?? ''),
                        $paymentAccount,
                        $payAmount,
                        $paymentDate,
                        $voucherNo,
                        $particular !== '' ? $particular : $expenseBillParticular,
                        $paymentMethodLabel,
                        $typeTransactionId
                    );

                    if ($advanceAssetAccount) {
                        $billEntry->update([
                            'advance_adjustment_asset_account_id' => $advanceAssetAccount->id,
                        ]);
                    }
                }

                $this->accountService->flushCache();
                $this->typeTransactionService->flushCache();

                return $billEntry->fresh(['expenseCategory', 'expenseHead', 'assetAccount', 'vendorAccount']);
            });
        });
    }

    public function payPayableBillEntry(FinanceBillEntry $billEntry, array $data): FinanceBillEntry
    {
        if ($billEntry->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => ['Only approved bills can be paid from Bills Payable.'],
            ]);
        }

        if (strtolower((string) $billEntry->payment_method) !== 'due') {
            throw ValidationException::withMessages([
                'payment_method' => ['This bill has already been settled or is not a due payable.'],
            ]);
        }

        $isAssetPurchase = ($billEntry->entry_type ?? 'expense_bill') === 'asset_purchase';
        $assetAccount = $isAssetPurchase ? $this->resolveAssetAccountForBill($billEntry) : null;
        $vendorAccount = $isAssetPurchase ? $this->resolveVendorAccountForBill($billEntry) : null;
        $expenseAccount = !$isAssetPurchase ? $this->resolveExpenseAccountForBill($billEntry) : null;

        if ($isAssetPurchase && !$assetAccount) {
            throw ValidationException::withMessages([
                'asset_account_id' => ['No asset account is linked to this purchase bill.'],
            ]);
        }

        if ($isAssetPurchase && !$vendorAccount) {
            throw ValidationException::withMessages([
                'vendor_account_id' => ['No vendor account is linked to this asset purchase bill.'],
            ]);
        }

        if (!$isAssetPurchase && !$expenseAccount) {
            throw ValidationException::withMessages([
                'account_id' => ['No expense account is linked to this bill.'],
            ]);
        }

        return $this->mutate(function () use ($billEntry, $data, $expenseAccount, $assetAccount, $vendorAccount, $isAssetPurchase) {
            $paymentMethod = strtolower((string) ($data['payment_method'] ?? 'cash'));
            if ($paymentMethod === 'due') {
                throw ValidationException::withMessages([
                    'payment_method' => ['Select cash, bank, or income link to settle this payable bill.'],
                ]);
            }

            if (!in_array($paymentMethod, ['cash', 'bank', 'income_link'], true)) {
                throw ValidationException::withMessages([
                    'payment_method' => ['Please select a valid payment method (Cash, Bank, or Income Link).'],
                ]);
            }

            return DB::transaction(function () use ($billEntry, $data, $expenseAccount, $assetAccount, $vendorAccount, $isAssetPurchase, $paymentMethod) {
                $billTotal = round((float) $billEntry->amount, 2);
                $alreadyPaid = round((float) ($billEntry->paid_amount ?? 0), 2);
                $remaining = round(max($billTotal - $alreadyPaid, 0), 2);
                $payAmount = round((float) ($data['pay_amount'] ?? $data['amount'] ?? 0), 2);

                if ($payAmount <= 0) {
                    throw ValidationException::withMessages([
                        'pay_amount' => ['Please enter a valid payment amount.'],
                    ]);
                }

                if ($payAmount > $remaining) {
                    throw ValidationException::withMessages([
                        'pay_amount' => ["Payment amount cannot exceed the remaining payable balance of {$remaining}."],
                    ]);
                }

                $paymentDate = $billEntry->payment_date?->format('Y-m-d') ?? now()->toDateString();
                $particular = trim((string) ($data['particular'] ?? $billEntry->particular));
                $referenceNo = trim((string) ($data['reference_no'] ?? $billEntry->reference_no ?? ''));
                $remarks = trim((string) ($data['remarks'] ?? $billEntry->remarks ?? ''));
                $approvalRemarks = trim((string) ($data['approval_remarks'] ?? ''));
                $voucherNo = trim((string) ($data['voucher_no'] ?? ''));
                if ($voucherNo === '') {
                    $voucherNo = $billEntry->voucher_no ?: $referenceNo ?: $this->nextVoucherNo('BILL', Carbon::parse($paymentDate));
                }

                $isIncomeLink = $paymentMethod === 'income_link';
                if ($isAssetPurchase && $isIncomeLink) {
                    throw ValidationException::withMessages([
                        'payment_method' => ['Income link settlement is not available for asset purchase bills.'],
                    ]);
                }

                $incomeAccount = null;
                $paymentAccount = null;
                $paymentFields = [];

                if ($isAssetPurchase) {
                    $assetAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->findOrFail($assetAccount->id);
                    $this->assertActiveFinanceAccount($assetAccount);

                    $vendorAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->findOrFail($vendorAccount->id);
                    $this->assertActiveFinanceAccount($vendorAccount);
                } else {
                    $expenseAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->findOrFail($expenseAccount->id);
                    $this->assertActiveFinanceAccount($expenseAccount);
                }

                if ($isIncomeLink) {
                    $incomeAccount = $this->accountService->resolveBillsPayableLinkedIncomeAccount();
                    $paymentFields = [
                        'payment_account_category' => $incomeAccount->category,
                        'payment_account_type' => null,
                        'payment_account_id' => $incomeAccount->id,
                        'payment_account_name' => $this->accountLabel($incomeAccount),
                    ];
                } else {
                    $paymentFields = $this->resolvePaymentAccountFields($data, $paymentMethod);

                    if (empty($paymentFields['payment_account_id'])) {
                        throw ValidationException::withMessages([
                            'payment_account_id' => ['Payment account is required to settle this payable bill.'],
                        ]);
                    }

                    $paymentAccount = FinanceAccount::query()
                        ->lockForUpdate()
                        ->findOrFail((int) $paymentFields['payment_account_id']);
                    $this->assertActiveFinanceAccount($paymentAccount);

                    if ((float) $paymentAccount->balance < $payAmount) {
                        throw ValidationException::withMessages([
                            'pay_amount' => ['Insufficient balance in the selected payment account.'],
                        ]);
                    }
                }

                $paymentMethodLabel = $this->resolveBillPaymentMethodLabel($paymentMethod, $paymentFields);
                $isFullyPaid = round($alreadyPaid + $payAmount, 2) >= $billTotal;
                $ledgerParticular = $particular !== ''
                    ? $particular
                    : ($isFullyPaid ? 'Bill payable settled' : 'Partial payable bill payment');
                $ledgerRemarks = $approvalRemarks !== ''
                    ? $approvalRemarks
                    : ($isFullyPaid
                        ? ($isIncomeLink ? 'Payable bill settled via income link' : 'Payable bill settled')
                        : ($isIncomeLink ? 'Partial payable bill via income link' : 'Partial payable bill payment'));

                $counterpartyAccount = $paymentAccount ?? $incomeAccount;
                $ledgerAccount = $isAssetPurchase ? $assetAccount : $expenseAccount;
                $typeTransaction = $this->createBillPaymentTransaction(
                    $payAmount,
                    $paymentDate,
                    $ledgerParticular,
                    $referenceNo,
                    $ledgerRemarks,
                    $voucherNo,
                    $counterpartyAccount,
                    $ledgerAccount,
                    $paymentFields
                );
                $typeTransactionId = $typeTransaction?->id;
                $advanceAssetAccountId = null;
                $settleVoucherNo = $voucherNo !== ''
                    ? sprintf('%s-P%s', $voucherNo, $typeTransactionId ?: now()->timestamp)
                    : sprintf('BILL-PAY-%d', $billEntry->id);

                if ($isAssetPurchase) {
                    // Vendor ledger: CR payment when settling due payable.
                    $this->postBillLedgerEntry(
                        $vendorAccount,
                        $billEntry->id,
                        $payAmount,
                        $paymentDate,
                        $ledgerParticular,
                        $settleVoucherNo,
                        $this->accountLabel($assetAccount),
                        $paymentMethodLabel,
                        $ledgerRemarks,
                        $typeTransactionId,
                        true
                    );
                } else {
                    // Expense head ledger: CR for the payment / settle amount.
                    $this->postBillLedgerEntry(
                        $expenseAccount,
                        $billEntry->id,
                        $payAmount,
                        $paymentDate,
                        $ledgerParticular,
                        $voucherNo,
                        $billEntry->client_name ?? '',
                        $paymentMethodLabel,
                        $ledgerRemarks,
                        $typeTransactionId,
                        true
                    );

                    // Clear {Head} Payable liability for the settled amount.
                    // Always key off the original due voucher so DRs match the CR
                    // even if the payment form supplies a different voucher_no.
                    $payableVoucherBase = trim((string) ($billEntry->voucher_no ?? ''));
                    if ($payableVoucherBase === '') {
                        $payableVoucherBase = $voucherNo !== '' ? $voucherNo : 'BILL-DUE-'.$billEntry->id;
                    }
                    $settleVoucherNo = sprintf(
                        '%s-P%s',
                        $payableVoucherBase,
                        $typeTransactionId ?: now()->timestamp
                    );

                    $expenseHead = $this->resolveExpenseHeadForBill($billEntry, $expenseAccount);
                    if ($expenseHead) {
                        $this->accountService->recordExpensePayableEntry(
                            $expenseHead,
                            $payAmount,
                            'dr',
                            $paymentDate,
                            $settleVoucherNo,
                            $typeTransactionId,
                            $ledgerParticular,
                            $paymentMethodLabel,
                            $ledgerRemarks,
                            (string) ($billEntry->client_name ?? '')
                        );
                    }

                    $this->postPartyLinkedAccountSettlementLedger(
                        $billEntry,
                        $expenseAccount,
                        $counterpartyAccount,
                        $paymentAccount,
                        $payAmount,
                        $paymentDate,
                        $ledgerParticular,
                        $settleVoucherNo,
                        $paymentMethodLabel,
                        $ledgerRemarks,
                        $typeTransactionId
                    );
                }

                if ($paymentAccount) {
                    // Payment account ledger: DR (cash/bank out).
                    $this->postBillLedgerEntry(
                        $paymentAccount,
                        $billEntry->id,
                        $payAmount,
                        $paymentDate,
                        $ledgerParticular,
                        $voucherNo,
                        $billEntry->linked_account_name ?? $billEntry->expense_cost_account_name ?? '',
                        $paymentMethodLabel ?? ucfirst($paymentMethod),
                        $ledgerRemarks,
                        $typeTransactionId,
                        false
                    );

                    $advanceAssetAccount = $this->postAdvanceAdjustmentAssetLedgerIfRequired(
                        $billEntry,
                        $data,
                        (string) ($paymentFields['payment_account_category'] ?? ''),
                        $paymentAccount,
                        $payAmount,
                        $paymentDate,
                        $settleVoucherNo,
                        $ledgerParticular,
                        $paymentMethodLabel,
                        $typeTransactionId
                    );

                    if ($advanceAssetAccount) {
                        $advanceAssetAccountId = $advanceAssetAccount->id;
                    }
                }

                if ($incomeAccount) {
                    // Linked income head ledger: CR (liability written back / settle without cash).
                    $this->postBillLedgerEntry(
                        $incomeAccount,
                        $billEntry->id,
                        $payAmount,
                        $paymentDate,
                        $ledgerParticular,
                        $voucherNo,
                        $billEntry->linked_account_name ?? $billEntry->expense_cost_account_name ?? '',
                        $paymentMethodLabel,
                        $ledgerRemarks,
                        $typeTransactionId,
                        true
                    );
                }

                $updateData = [
                    'paid_amount' => round($alreadyPaid + $payAmount, 2),
                    'approval_remarks' => $approvalRemarks,
                ];

                if ($advanceAssetAccountId) {
                    $updateData['advance_adjustment_asset_account_id'] = $advanceAssetAccountId;
                }

                if ($isFullyPaid) {
                    // Keep the original due voucher_no so Trial Balance / backfill
                    // can still match CR and settlement DR rows for this bill.
                    $updateData = array_merge($updateData, [
                        'particular' => $particular,
                        'reference_no' => $referenceNo,
                        'remarks' => $remarks,
                        'payment_method' => $paymentMethod,
                        ...$paymentFields,
                    ]);
                    if (trim((string) ($billEntry->voucher_no ?? '')) === '' && $voucherNo !== '') {
                        $updateData['voucher_no'] = $voucherNo;
                    }
                }

                $billEntry->update($updateData);

                $this->accountService->flushCache();
                $this->typeTransactionService->flushCache();

                return $billEntry->fresh(['expenseCategory', 'expenseHead', 'assetAccount', 'vendorAccount']);
            });
        });
    }

    public function managerApproveBillEntry(FinanceBillEntry $billEntry, array $data): FinanceBillEntry
    {
        if ($billEntry->status === 'approved') {
            throw ValidationException::withMessages([
                'status' => ['This bill entry is already paid/approved.'],
            ]);
        }

        if ($billEntry->status === 'pending') {
            throw ValidationException::withMessages([
                'status' => ['This bill is already manager-approved and waiting in Bills To Pay.'],
            ]);
        }

        if ($billEntry->status === 'rejected') {
            throw ValidationException::withMessages([
                'status' => ['Rejected bills cannot be manager-approved.'],
            ]);
        }

        if ($billEntry->status !== 'submitted') {
            throw ValidationException::withMessages([
                'status' => ['Only submitted bills can be approved by a manager.'],
            ]);
        }

        return $this->mutate(function () use ($billEntry, $data) {
            $billEntry->update([
                'approval_remarks' => trim((string) ($data['approval_remarks'] ?? $billEntry->approval_remarks ?? '')),
                'status' => 'pending',
                'manager_approved_at' => now(),
                'manager_approved_by' => trim((string) ($data['approved_by'] ?? 'Manager')),
                'rejected_at' => null,
            ]);

            return $billEntry->fresh(['expenseCategory', 'expenseHead']);
        });
    }

    /**
     * Manager-approve multiple submitted bill entries that belong to the same batch.
     *
     * @param  array<int, int>  $ids
     * @return array<int, FinanceBillEntry>
     */
    public function managerApproveBillEntryBatch(array $ids, array $data): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (count($ids) < 1) {
            throw ValidationException::withMessages([
                'ids' => ['Select at least one bill entry to approve.'],
            ]);
        }

        return $this->mutate(function () use ($ids, $data) {
            return DB::transaction(function () use ($ids, $data) {
                $entries = FinanceBillEntry::query()
                    ->with(['expenseCategory', 'expenseHead'])
                    ->whereIn('id', $ids)
                    ->lockForUpdate()
                    ->get();

                if ($entries->count() !== count($ids)) {
                    throw ValidationException::withMessages([
                        'ids' => ['One or more selected bill entries were not found.'],
                    ]);
                }

                $batchKeys = $entries->map(fn (FinanceBillEntry $entry) => $this->resolveBillBatchKey($entry))->unique()->values();

                if ($batchKeys->count() !== 1) {
                    throw ValidationException::withMessages([
                        'ids' => ['Bulk approve is only allowed for bills from the same batch.'],
                    ]);
                }

                foreach ($entries as $entry) {
                    if ($entry->status !== 'submitted') {
                        throw ValidationException::withMessages([
                            'ids' => ["Bill #{$entry->voucher_no} is not submitted and cannot be bulk-approved."],
                        ]);
                    }
                }

                $approvedBy = trim((string) ($data['approved_by'] ?? 'Manager'));
                $approvalRemarks = trim((string) ($data['approval_remarks'] ?? ''));
                $now = now();

                foreach ($entries as $entry) {
                    $entry->update([
                        'approval_remarks' => $approvalRemarks !== ''
                            ? $approvalRemarks
                            : trim((string) ($entry->approval_remarks ?? '')),
                        'status' => 'pending',
                        'manager_approved_at' => $now,
                        'manager_approved_by' => $approvedBy,
                        'rejected_at' => null,
                    ]);
                }

                return FinanceBillEntry::query()
                    ->with(['expenseCategory', 'expenseHead'])
                    ->whereIn('id', $ids)
                    ->orderBy('id')
                    ->get()
                    ->all();
            });
        });
    }

    public function resolveBillBatchKey(FinanceBillEntry $entry): string
    {
        $requestNo = trim((string) ($entry->request_no ?? ''));
        if ($requestNo !== '') {
            return 'req:' . $requestNo;
        }

        $batchRef = trim((string) ($entry->batch_ref ?? ''));
        if ($batchRef !== '') {
            return 'batch:' . $batchRef;
        }

        return 'id:' . $entry->id;
    }

    public function rejectBillEntry(FinanceBillEntry $billEntry, array $data): FinanceBillEntry
    {
        if ($billEntry->status === 'approved') {
            throw ValidationException::withMessages([
                'status' => ['Approved bills cannot be rejected.'],
            ]);
        }

        if (!in_array($billEntry->status, ['submitted', 'pending'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only submitted or pending bills can be rejected.'],
            ]);
        }

        return $this->mutate(function () use ($billEntry, $data) {
            $billEntry->update([
                'amount' => (float) ($data['amount'] ?? $billEntry->amount),
                'particular' => trim((string) ($data['particular'] ?? $billEntry->particular)),
                'reference_no' => trim((string) ($data['reference_no'] ?? $billEntry->reference_no ?? '')),
                'remarks' => trim((string) ($data['remarks'] ?? $billEntry->remarks ?? '')),
                'approval_remarks' => trim((string) ($data['approval_remarks'] ?? '')),
                'status' => 'rejected',
                'rejected_at' => now(),
                'approved_by' => trim((string) ($data['approved_by'] ?? 'Manager')),
                'approved_at' => null,
            ]);

            return $billEntry->fresh(['expenseCategory', 'expenseHead']);
        });
    }

    public function getSummary(): array
    {
        return [
            'total_count' => $this->repository->countByStatus(),
            'submitted_count' => $this->repository->countByStatus('submitted'),
            'submitted_expense_count' => $this->repository->countByStatusAndEntryType('submitted', 'expense_bill'),
            'submitted_purchase_count' => $this->repository->countByStatusAndEntryType('submitted', 'asset_purchase'),
            'pending_count' => $this->repository->countByStatus('pending'),
            'approved_count' => $this->repository->countByStatus('approved'),
            'paid_count' => $this->repository->countPaid(),
            'payable_count' => $this->repository->countPayable(),
            'rejected_count' => $this->repository->countByStatus('rejected'),
        ];
    }

    public function getTotalPaidByHead(int $headId): float
    {
        return $this->repository->sumAmountByHead($headId);
    }

    private function buildCreatePayload(array $data, bool $generateVoucher = true): array
    {
        $entryType = strtolower(trim((string) ($data['entry_type'] ?? 'expense_bill')));
        if (!in_array($entryType, ['expense_bill', 'asset_purchase'], true)) {
            $entryType = 'expense_bill';
        }

        if ($entryType === 'asset_purchase') {
            return $this->buildAssetPurchaseCreatePayload($data, $generateVoucher);
        }

        $categoryId = (int) ($data['category_id'] ?? $data['expense_category_id'] ?? 0);
        $headId = (int) ($data['head_id'] ?? $data['expense_head_id'] ?? 0);
        $paymentDate = $data['payment_date'] ?? now()->toDateString();

        $category = ExpenseCategory::find($categoryId);
        $head = ExpenseHead::find($headId);

        if (!$category || !$head || (int) $head->expense_category_id !== $categoryId) {
            throw ValidationException::withMessages([
                'head_id' => ['Selected expense head is invalid.'],
            ]);
        }

        if (strtolower((string) $head->status) !== 'active') {
            throw ValidationException::withMessages([
                'head_id' => ['Selected expense head is not active.'],
            ]);
        }

        $referenceNo = trim((string) ($data['reference_no'] ?? ''));
        $voucherNo = $referenceNo !== '' ? $referenceNo : ($generateVoucher ? $this->nextVoucherNo('BILL', Carbon::parse($paymentDate)) : null);

        $payload = [
            'entry_type' => 'expense_bill',
            'expense_category_id' => $categoryId,
            'expense_head_id' => $headId,
            'amount' => (float) $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'payment_date' => $paymentDate,
            'particular' => trim((string) ($data['particular'] ?? sprintf('%s - %s', $category->name, $head->name))),
            'reference_no' => $referenceNo,
            'voucher_no' => $voucherNo,
            'batch_ref' => trim((string) ($data['batch_ref'] ?? '')) ?: null,
            'request_no' => trim((string) ($data['request_no'] ?? '')) ?: null,
            'remarks' => trim((string) ($data['remarks'] ?? '')),
            'receipt_path' => $data['receipt_path'] ?? null,
            'status' => $this->resolveCreateStatus($data),
            'is_manual_request' => $this->resolveCreateStatus($data) === 'pending',
            'approval_remarks' => '',
            'linked_account_category' => $data['linked_account_category'] ?? null,
            'linked_account_id' => !empty($data['linked_account_id']) ? (int) $data['linked_account_id'] : null,
            'linked_account_name' => $data['linked_account_name'] ?? null,
            'linked_account_type' => $data['linked_account_type'] ?? null,
            'expense_cost_type' => $data['expense_cost_type'] ?? null,
            'expense_cost_account_id' => !empty($data['expense_cost_account_id']) ? (int) $data['expense_cost_account_id'] : null,
            'expense_cost_account_name' => $data['expense_cost_account_name'] ?? null,
            'expense_cost_category_name' => $data['expense_cost_category_name'] ?? null,
            ...$this->resolveRequestedByFields($data),
        ];

        $applicationId = (int) ($data['application_id'] ?? 0);
        if ($applicationId > 0) {
            $this->assertNoDuplicateApplicationBills([$applicationId], $headId);
            $payload = [...$payload, ...$this->resolveApplicationMeta($applicationId)];
        }

        $demandLetterId = (int) ($data['demand_letter_id'] ?? $data['work_order_id'] ?? 0);
        if ($demandLetterId > 0) {
            $payload = [...$payload, ...$this->resolveDemandLetterMeta($demandLetterId)];
        }

        return $payload;
    }

    private function buildAssetPurchaseCreatePayload(array $data, bool $generateVoucher = true): array
    {
        $assetAccountId = (int) ($data['asset_account_id'] ?? 0);
        $assetAccount = FinanceAccount::query()
            ->where('id', $assetAccountId)
            ->where('category', 'asset')
            ->where('link_to_purchase', true)
            ->where('status', 'active')
            ->first();

        if (!$assetAccount) {
            throw ValidationException::withMessages([
                'asset_account_id' => ['Please select a valid purchase-linked asset account.'],
            ]);
        }

        $vendorAccountId = (int) ($data['vendor_account_id'] ?? 0);
        $vendorAccount = FinanceAccount::query()
            ->where('id', $vendorAccountId)
            ->where('category', 'vendor')
            ->where('status', 'active')
            ->first();

        if (!$vendorAccount) {
            throw ValidationException::withMessages([
                'vendor_account_id' => ['Please select a valid vendor account.'],
            ]);
        }

        $paymentDate = $data['payment_date'] ?? now()->toDateString();
        $referenceNo = trim((string) ($data['reference_no'] ?? ''));
        $voucherNo = $referenceNo !== '' ? $referenceNo : ($generateVoucher ? $this->nextVoucherNo('BILL', Carbon::parse($paymentDate)) : null);
        $accountName = trim((string) $assetAccount->account_name);

        return [
            'entry_type' => 'asset_purchase',
            'expense_category_id' => null,
            'expense_head_id' => null,
            'asset_account_id' => $assetAccount->id,
            'vendor_account_id' => $vendorAccount->id,
            'amount' => (float) $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'payment_date' => $paymentDate,
            'particular' => trim((string) ($data['particular'] ?? "Asset Purchase - {$accountName}")),
            'reference_no' => $referenceNo,
            'voucher_no' => $voucherNo,
            'batch_ref' => trim((string) ($data['batch_ref'] ?? '')) ?: null,
            'request_no' => trim((string) ($data['request_no'] ?? '')) ?: null,
            'remarks' => trim((string) ($data['remarks'] ?? '')),
            'receipt_path' => $data['receipt_path'] ?? null,
            'status' => $this->resolveCreateStatus($data),
            'is_manual_request' => $this->resolveCreateStatus($data) === 'pending',
            'approval_remarks' => '',
            'linked_account_category' => null,
            'linked_account_id' => null,
            'linked_account_name' => null,
            'linked_account_type' => null,
            'expense_cost_type' => null,
            'expense_cost_account_id' => null,
            'expense_cost_account_name' => null,
            'expense_cost_category_name' => null,
            ...$this->resolveRequestedByFields($data),
        ];
    }

    private function resolveCreateStatus(array $data): string
    {
        $status = strtolower(trim((string) ($data['status'] ?? 'submitted')));

        return in_array($status, ['submitted', 'pending'], true) ? $status : 'submitted';
    }

    private function resolveApplicationMeta(int $applicationId): array
    {
        $application = Application::query()
            ->with(['jobList.client.user', 'currentProcess.process'])
            ->find($applicationId);

        if (!$application) {
            throw ValidationException::withMessages([
                'application_id' => ['Selected application was not found.'],
            ]);
        }

        $job = $application->jobList;
        $candidateName = trim(($application->given_name ?? '') . ' ' . ($application->sur_name ?? ''));

        return [
            'application_id' => $application->id,
            'job_list_id' => $application->job_list_id,
            'candidate_name' => $candidateName,
            'passport_no' => $application->passport_no,
            'application_status' => $application->resolved_current_process ?? $application->application_status,
            'job_name' => $job?->name,
            'job_code' => $job?->job_code,
            'client_name' => $job?->client?->user?->name ?? '',
        ];
    }

    private function resolveDemandLetterMeta(int $workOrderId): array
    {
        $workOrder = WorkOrder::query()
            ->with(['client.user', 'client.country'])
            ->find($workOrderId);

        if (!$workOrder) {
            throw ValidationException::withMessages([
                'demand_letter_id' => ['Selected demand letter was not found.'],
            ]);
        }

        return [
            'work_order_id' => $workOrder->id,
            'demand_letter' => $workOrder->work_order_id,
            'client_name' => $workOrder->client?->user?->name ?? '',
            'demand_letter_country' => $workOrder->client?->country?->name ?? '',
        ];
    }

    private function resolveRequestedByFields(array $data): array
    {
        $user = Auth::user();

        return [
            'requested_by_id' => $data['requested_by_id'] ?? $user?->id,
            'requested_by_name' => trim((string) ($data['requested_by_name'] ?? $user?->name ?? '')),
            'requested_by_email' => trim((string) ($data['requested_by_email'] ?? $user?->email ?? '')),
            'requested_by_type' => trim((string) ($data['requested_by_type'] ?? $user?->type ?? '')),
            'requested_at' => now(),
        ];
    }

    /**
     * Manually requested bills skip the manager approval screen, so the approving
     * accountant must record which manager approved the bill offline.
     */
    private function resolveManualApprovalFields(FinanceBillEntry $billEntry, array $data): array
    {
        if (!$billEntry->is_manual_request) {
            return [];
        }

        $managerId = (int) ($data['manual_approval_manager_id'] ?? 0);
        if ($managerId <= 0) {
            throw ValidationException::withMessages([
                'manual_approval_manager_id' => ['Please select the manager who approved this manual bill.'],
            ]);
        }

        $manager = Employee::query()->with('user')->find($managerId);
        if (!$manager || (int) $manager->manager_approval !== 1) {
            throw ValidationException::withMessages([
                'manual_approval_manager_id' => ['Selected employee is not an approval manager.'],
            ]);
        }

        $fields = [
            'manual_approval_manager_id' => $manager->id,
            'manual_approval_manager_name' => trim((string) $manager->user?->name),
        ];

        if (!empty($data['manual_approval_path'])) {
            $fields['manual_approval_path'] = $data['manual_approval_path'];
        }

        return $fields;
    }

    private function resolvePaymentAccountFields(array $data, string $paymentMethod): array
    {
        if ($paymentMethod === 'due') {
            return [
                'payment_account_category' => null,
                'payment_account_type' => null,
                'payment_account_id' => null,
                'payment_account_name' => null,
            ];
        }

        return [
            'payment_account_category' => $data['payment_account_category'] ?? null,
            'payment_account_type' => $data['payment_account_type'] ?? null,
            'payment_account_id' => !empty($data['payment_account_id']) ? (int) $data['payment_account_id'] : null,
            'payment_account_name' => $data['payment_account_name'] ?? null,
        ];
    }

    /**
     * @param  int[]  $applicationIds
     */
    private function assertNoDuplicateApplicationBills(array $applicationIds, int $headId): void
    {
        if ($headId <= 0 || empty($applicationIds)) {
            return;
        }

        $duplicateIds = FinanceBillEntry::query()
            ->where('expense_head_id', $headId)
            ->whereIn('application_id', $applicationIds)
            ->whereIn('status', ['submitted', 'pending', 'approved'])
            ->pluck('application_id')
            ->unique()
            ->values()
            ->all();

        if (empty($duplicateIds)) {
            return;
        }

        throw ValidationException::withMessages([
            'application_ids' => [
                'One or more selected candidates already have a submitted, pending, or approved bill for this expense head.',
            ],
        ]);
    }

    private function nextVoucherNo(string $prefix = 'BILL', ?Carbon $date = null): string
    {
        $date = $date ?? now();
        $sequence = $this->nextSequenceForColumn($prefix, 'voucher_no', $date) + 1;

        return sprintf('%s-%03d/%s', $prefix, $sequence, $date->format('y'));
    }

    private function nextRequestNo(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $sequence = $this->nextSequenceForColumn('REQ', 'request_no', $date) + 1;

        return sprintf('REQ-%03d/%s', $sequence, $date->format('y'));
    }

    private function nextSequenceForColumn(string $prefix, string $column, Carbon $date): int
    {
        return FinanceBillEntry::query()
            ->where($column, 'like', "{$prefix}-%/%")
            ->whereYear('payment_date', $date->year)
            ->count();
    }

    private function nextVoucherSequence(string $prefix = 'BILL', ?Carbon $date = null): int
    {
        $date = $date ?? now();

        return $this->nextSequenceForColumn($prefix, 'voucher_no', $date);
    }

    private const EXPENSE_ACCOUNT_CATEGORIES = [
        'direct_expense',
        'client_recruitment',
        'operating_expense',
    ];

    /**
     * Ensure approved bills have expense-head ledger DR (+ CR when paid by cash/bank).
     * Needed when older payments only hit cash/bank, or DR was posted without settlement CR.
     */
    public function backfillApprovedBillExpenseLedgers(?string $toDate = null): int
    {
        $query = FinanceBillEntry::query()
            ->where('status', 'approved')
            ->whereNotNull('expense_head_id');

        if ($toDate) {
            $query->whereDate('payment_date', '<=', $toDate);
        }

        $posted = 0;

        foreach ($query->get() as $billEntry) {
            $expenseAccount = $this->resolveExpenseAccountForBill($billEntry);
            if (!$expenseAccount) {
                continue;
            }

            $amount = round((float) $billEntry->amount, 2);
            if ($amount <= 0) {
                continue;
            }

            $paymentDate = $billEntry->payment_date?->format('Y-m-d') ?? now()->toDateString();
            $particular = trim((string) ($billEntry->particular ?? '')) ?: 'Bill approved';
            $voucherNo = trim((string) ($billEntry->voucher_no ?? ''))
                ?: trim((string) ($billEntry->reference_no ?? ''))
                ?: sprintf('BILL-%03d', $billEntry->id);
            $paymentMethod = strtolower((string) ($billEntry->payment_method ?? ''));
            $isDuePayment = $paymentMethod === 'due';
            $paymentMethodLabel = !$isDuePayment && $paymentMethod !== ''
                ? ucfirst($paymentMethod)
                : null;

            $expenseAccount = FinanceAccount::query()->find($expenseAccount->id);
            if (!$expenseAccount) {
                continue;
            }

            $hasExpenseDebit = FinanceAccountLedgerEntry::query()
                ->where('finance_bill_entry_id', $billEntry->id)
                ->where('finance_account_id', $expenseAccount->id)
                ->where('dr_amount', '>', 0)
                ->exists();

            if (!$hasExpenseDebit) {
                $this->postBillLedgerEntry(
                    $expenseAccount,
                    $billEntry->id,
                    $amount,
                    $paymentDate,
                    $particular,
                    $voucherNo,
                    $billEntry->client_name ?? '',
                    $paymentMethodLabel,
                    'Backfilled expense charge',
                    null,
                    false
                );
                $posted++;
            }

            // Cash/bank paid bills also need CR against the DR on the expense ledger.
            if (!$isDuePayment) {
                $hasExpenseCredit = FinanceAccountLedgerEntry::query()
                    ->where('finance_bill_entry_id', $billEntry->id)
                    ->where('finance_account_id', $expenseAccount->id)
                    ->where('cr_amount', '>', 0)
                    ->exists();

                if (!$hasExpenseCredit) {
                    $this->postBillLedgerEntry(
                        $expenseAccount,
                        $billEntry->id,
                        $amount,
                        $paymentDate,
                        $particular,
                        $voucherNo,
                        $billEntry->client_name ?? '',
                        $paymentMethodLabel,
                        'Backfilled expense payment settlement',
                        null,
                        true
                    );
                    $posted++;
                }
            }
        }

        if ($posted > 0) {
            $this->accountService->flushCache();
        }

        return $posted;
    }

    private function resolveExpenseAccountForBill(FinanceBillEntry $billEntry): ?FinanceAccount
    {
        $accountId = (int) ($billEntry->expense_cost_account_id ?: 0);
        if ($accountId <= 0) {
            $accountId = (int) ($billEntry->linked_account_id ?: 0);
        }

        if ($accountId > 0) {
            $account = FinanceAccount::query()->find($accountId);
            if (
                $account
                && in_array((string) $account->category, self::EXPENSE_ACCOUNT_CATEGORIES, true)
            ) {
                return $account;
            }
        }

        $headId = (int) ($billEntry->expense_head_id ?? 0);
        if ($headId <= 0) {
            return null;
        }

        $existing = FinanceAccount::query()
            ->where('expense_head_id', $headId)
            ->whereIn('category', self::EXPENSE_ACCOUNT_CATEGORIES)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return $existing;
        }

        $expenseHead = ExpenseHead::query()->find($headId);
        if (!$expenseHead) {
            return null;
        }

        return $this->accountService->ensureExpenseHeadAccount($expenseHead);
    }

    private function resolveAssetAccountForBill(FinanceBillEntry $billEntry): ?FinanceAccount
    {
        $accountId = (int) ($billEntry->asset_account_id ?? $billEntry->linked_account_id ?? 0);
        if ($accountId <= 0) {
            return null;
        }

        return FinanceAccount::query()
            ->where('id', $accountId)
            ->where('category', 'asset')
            ->where('status', 'active')
            ->first();
    }

    private function resolveVendorAccountForBill(FinanceBillEntry $billEntry): ?FinanceAccount
    {
        $accountId = (int) ($billEntry->vendor_account_id ?? 0);
        if ($accountId <= 0) {
            return null;
        }

        return FinanceAccount::query()
            ->where('id', $accountId)
            ->where('category', 'vendor')
            ->where('status', 'active')
            ->first();
    }

    private function resolvePartyLinkedAccountForBill(FinanceBillEntry $billEntry): ?FinanceAccount
    {
        $linkedAccountId = (int) ($billEntry->linked_account_id ?? 0);
        if ($linkedAccountId <= 0) {
            return null;
        }

        $expenseCostAccountId = (int) ($billEntry->expense_cost_account_id ?? 0);
        if ($expenseCostAccountId > 0 && $linkedAccountId === $expenseCostAccountId) {
            return null;
        }

        $account = FinanceAccount::query()
            ->where('id', $linkedAccountId)
            ->where('status', 'active')
            ->first();

        if (!$account) {
            return null;
        }

        if (in_array((string) $account->category, self::EXPENSE_ACCOUNT_CATEGORIES, true)) {
            return null;
        }

        if ((string) $account->category === 'main') {
            return null;
        }

        return $account;
    }

    /**
     * When paying from a non-main account that is also the bill's linked party account,
     * only the payment DR should appear on that ledger (not vendor-mirror DR/CR rows).
     */
    private function isPartyLinkedAccountSameAsNonMainPaymentAccount(
        ?FinanceAccount $partyLinkedAccount,
        ?FinanceAccount $paymentAccount
    ): bool {
        if (!$partyLinkedAccount || !$paymentAccount) {
            return false;
        }

        if ((int) $partyLinkedAccount->id !== (int) $paymentAccount->id) {
            return false;
        }

        $paymentCategory = strtolower(trim((string) ($paymentAccount->category ?? '')));

        return $paymentCategory !== '' && $paymentCategory !== 'main';
    }

    private function postPartyLinkedAccountApprovalLedgers(
        FinanceBillEntry $billEntry,
        ?FinanceAccount $expenseAccount,
        ?FinanceAccount $paymentAccount,
        float $billTotal,
        float $payAmount,
        string $paymentDate,
        string $particular,
        string $voucherNo,
        bool $hasDueRemaining,
        bool $hasCashPayment,
        ?string $paymentMethodLabel,
        string $approvalRemarks,
        string $remarks,
        ?int $typeTransactionId
    ): void {
        $partyLinkedAccount = $this->resolvePartyLinkedAccountForBill($billEntry);
        if (!$partyLinkedAccount) {
            return;
        }

        $partyLinkedAccount = FinanceAccount::query()
            ->lockForUpdate()
            ->findOrFail($partyLinkedAccount->id);
        $this->assertActiveFinanceAccount($partyLinkedAccount);

        if ($this->isPartyLinkedAccountSameAsNonMainPaymentAccount($partyLinkedAccount, $paymentAccount)) {
            return;
        }

        $counterpartyLabel = $expenseAccount
            ? $this->accountLabel($expenseAccount)
            : trim((string) ($billEntry->expense_cost_account_name ?? $billEntry->client_name ?? ''));

        // Party linked ledger mirrors vendor asset pattern: DR charge, CR payment.
        $this->postBillLedgerEntry(
            $partyLinkedAccount,
            $billEntry->id,
            $billTotal,
            $paymentDate,
            $particular,
            $voucherNo,
            $counterpartyLabel,
            $hasDueRemaining ? null : ($hasCashPayment ? $paymentMethodLabel : null),
            $approvalRemarks ?: $remarks ?: 'Bill approved',
            $typeTransactionId,
            false
        );

        if (!$hasCashPayment) {
            return;
        }

        $settleVoucherNo = $voucherNo !== ''
            ? sprintf('%s-P%s', $voucherNo, $typeTransactionId ?: now()->timestamp)
            : sprintf('BILL-PAY-%d', $billEntry->id);
        $paymentCounterparty = $paymentAccount
            ? $this->accountLabel($paymentAccount)
            : $counterpartyLabel;

        $this->postBillLedgerEntry(
            $partyLinkedAccount,
            $billEntry->id,
            $payAmount,
            $paymentDate,
            $particular,
            $settleVoucherNo,
            $paymentCounterparty,
            $paymentMethodLabel,
            $approvalRemarks ?: ($hasDueRemaining
                ? 'Partial bill payment approved'
                : 'Bill payment approved'),
            $typeTransactionId,
            true
        );
    }

    private function postPartyLinkedAccountSettlementLedger(
        FinanceBillEntry $billEntry,
        ?FinanceAccount $expenseAccount,
        ?FinanceAccount $counterpartyAccount,
        ?FinanceAccount $paymentAccount,
        float $payAmount,
        string $paymentDate,
        string $particular,
        string $settleVoucherNo,
        ?string $paymentMethodLabel,
        string $remarks,
        ?int $typeTransactionId
    ): void {
        $partyLinkedAccount = $this->resolvePartyLinkedAccountForBill($billEntry);
        if (!$partyLinkedAccount) {
            return;
        }

        $partyLinkedAccount = FinanceAccount::query()
            ->lockForUpdate()
            ->findOrFail($partyLinkedAccount->id);
        $this->assertActiveFinanceAccount($partyLinkedAccount);

        if ($this->isPartyLinkedAccountSameAsNonMainPaymentAccount($partyLinkedAccount, $paymentAccount)) {
            return;
        }

        $counterpartyLabel = $counterpartyAccount
            ? $this->accountLabel($counterpartyAccount)
            : ($expenseAccount ? $this->accountLabel($expenseAccount) : '');

        $this->postBillLedgerEntry(
            $partyLinkedAccount,
            $billEntry->id,
            $payAmount,
            $paymentDate,
            $particular,
            $settleVoucherNo,
            $counterpartyLabel,
            $paymentMethodLabel,
            $remarks,
            $typeTransactionId,
            true
        );
    }

    private function requiresAdvanceAdjustmentAsset(?string $paymentAccountCategory): bool
    {
        $category = strtolower(trim((string) ($paymentAccountCategory ?? '')));

        return $category !== '' && $category !== 'main';
    }

    private function resolveAdvanceAdjustmentAssetAccount(
        array $data,
        ?string $paymentAccountCategory
    ): ?FinanceAccount {
        if (!$this->requiresAdvanceAdjustmentAsset($paymentAccountCategory)) {
            return null;
        }

        $assetAccountId = (int) ($data['advance_adjustment_asset_account_id'] ?? 0);
        if ($assetAccountId <= 0) {
            throw ValidationException::withMessages([
                'advance_adjustment_asset_account_id' => ['Please select an asset account for advanced adjustment.'],
            ]);
        }

        $account = FinanceAccount::query()
            ->where('id', $assetAccountId)
            ->where('category', 'asset')
            ->where('link_to_purchase', false)
            ->where('status', 'active')
            ->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'advance_adjustment_asset_account_id' => ['Please select a valid asset account that is not linked to purchase.'],
            ]);
        }

        return $account;
    }

    private function formatAdvanceAdjustmentParticular(
        FinanceBillEntry $billEntry,
        FinanceAccount $paymentAccount,
        FinanceAccount $assetAccount,
        string $billParticular,
        string $voucherNo
    ): string {
        $paymentLabel = $this->accountLabel($paymentAccount);
        $assetName = trim((string) $assetAccount->account_name);
        $headName = trim((string) ($billEntry->expenseHead?->name ?? ''));
        $voucher = trim($voucherNo) !== '' ? trim($voucherNo) : ('BILL-'.$billEntry->id);
        $billParticular = trim($billParticular);

        $segments = [
            'Advanced Adjustment',
            "via {$paymentLabel}",
        ];

        if ($assetName !== '') {
            $segments[] = "on {$assetName}";
        }

        if ($headName !== '') {
            $segments[] = "for {$headName}";
        }

        if ($billParticular !== '') {
            $segments[] = "— {$billParticular}";
        }

        $segments[] = "({$voucher})";

        return implode(' ', $segments);
    }

    private function postAdvanceAdjustmentAssetLedgerIfRequired(
        FinanceBillEntry $billEntry,
        array $data,
        string $paymentAccountCategory,
        FinanceAccount $paymentAccount,
        float $amount,
        string $paymentDate,
        string $voucherNo,
        string $billParticular,
        ?string $paymentMethodLabel,
        ?int $typeTransactionId
    ): ?FinanceAccount {
        if ($amount <= 0 || !$this->requiresAdvanceAdjustmentAsset($paymentAccountCategory)) {
            return null;
        }

        $assetAccount = $this->resolveAdvanceAdjustmentAssetAccount($data, $paymentAccountCategory);
        if (!$assetAccount) {
            return null;
        }

        $assetAccount = FinanceAccount::query()
            ->lockForUpdate()
            ->findOrFail($assetAccount->id);
        $this->assertActiveFinanceAccount($assetAccount);

        $particular = $this->formatAdvanceAdjustmentParticular(
            $billEntry,
            $paymentAccount,
            $assetAccount,
            $billParticular,
            $voucherNo
        );

        $this->postBillLedgerEntry(
            $assetAccount,
            $billEntry->id,
            $amount,
            $paymentDate,
            $particular,
            $voucherNo,
            $this->accountLabel($paymentAccount),
            $paymentMethodLabel,
            $particular,
            $typeTransactionId,
            true
        );

        return $assetAccount;
    }

    private function resolveExpenseHeadForBill(
        FinanceBillEntry $billEntry,
        ?FinanceAccount $expenseAccount = null
    ): ?ExpenseHead {
        $headId = (int) ($billEntry->expense_head_id ?? 0);
        if ($headId <= 0 && $expenseAccount) {
            $headId = (int) ($expenseAccount->expense_head_id ?? 0);
        }

        if ($headId <= 0) {
            return null;
        }

        return ExpenseHead::query()->find($headId);
    }

    /**
     * Map a resolved expense ledger onto bill linked/cost account fields.
     *
     * @return array<string, mixed>
     */
    private function buildExpenseAccountLinkFields(
        FinanceAccount $expenseAccount,
        FinanceBillEntry $billEntry
    ): array {
        $accountCategory = (string) ($expenseAccount->category ?? '');
        $costType = match ($accountCategory) {
            'direct_expense' => 'direct_cost',
            'client_recruitment' => 'client_recruitment_cost',
            'operating_expense' => 'operating_cost',
            default => trim((string) ($billEntry->expense_cost_type ?? $billEntry->linked_account_category ?? '')),
        };

        $accountName = $this->accountLabel($expenseAccount);
        $fields = [];

        if (!(int) ($billEntry->linked_account_id ?? 0)) {
            $fields['linked_account_id'] = $expenseAccount->id;
            $fields['linked_account_name'] = $accountName;
            $fields['linked_account_category'] = $costType !== ''
                ? $costType
                : ($billEntry->linked_account_category ?: $accountCategory);
            $fields['linked_account_type'] = $billEntry->linked_account_type;
        }

        if (!(int) ($billEntry->expense_cost_account_id ?? 0)) {
            $fields['expense_cost_account_id'] = $expenseAccount->id;
            $fields['expense_cost_account_name'] = $accountName;
            if ($costType !== '') {
                $fields['expense_cost_type'] = $costType;
            }
            if (trim((string) ($billEntry->expense_cost_category_name ?? '')) === '') {
                $fields['expense_cost_category_name'] = match ($costType) {
                    'direct_cost' => 'Direct Expense',
                    'client_recruitment_cost' => 'Client Recruitment',
                    'operating_cost' => 'Operating Expense',
                    default => null,
                };
            }
        }

        return $fields;
    }

    /**
     * Fill missing expense account links on existing approved due bills.
     */
    public function backfillMissingBillExpenseAccountLinks(): int
    {
        $bills = FinanceBillEntry::query()
            ->where('status', 'approved')
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->where(function ($query) {
                $query
                    ->whereNull('linked_account_id')
                    ->orWhereNull('expense_cost_account_id');
            })
            ->orderBy('id')
            ->get();

        $updated = 0;

        foreach ($bills as $bill) {
            $expenseAccount = $this->resolveExpenseAccountForBill($bill);
            if (!$expenseAccount) {
                continue;
            }

            $fields = $this->buildExpenseAccountLinkFields($expenseAccount, $bill);
            if ($fields === []) {
                continue;
            }

            $bill->update($fields);
            $updated++;
        }

        if ($updated > 0) {
            $this->flushCache();
        }

        return $updated;
    }

    private function createBillPaymentTransaction(
        float $amount,
        string $paymentDate,
        string $particular,
        string $referenceNo,
        string $remarks,
        string $voucherNo,
        ?FinanceAccount $paymentAccount,
        ?FinanceAccount $expenseAccount,
        array $paymentFields
    ): ?FinanceAccountTypeTransaction {
        if (!$paymentAccount && !$expenseAccount) {
            return null;
        }

        $resolvedParticular = $particular !== '' ? $particular : 'Bill Payment';

        if ($paymentAccount && $expenseAccount) {
            return FinanceAccountTypeTransaction::query()->create([
                'transaction_type' => 'bill_payment',
                'amount' => $amount,
                'transaction_date' => $paymentDate,
                'particular' => $resolvedParticular,
                'reference_no' => $referenceNo ?: null,
                'remarks' => $remarks !== '' ? $remarks : 'Bill payment approved',
                'voucher_no' => $voucherNo,
                'from_account_category' => $paymentAccount->category,
                'from_main_account_type' => (string) ($paymentFields['payment_account_type'] ?? ''),
                'from_account_id' => $paymentAccount->id,
                'from_account_label' => $this->accountLabel($paymentAccount),
                'to_account_category' => $expenseAccount->category,
                'to_main_account_type' => '',
                'to_account_id' => $expenseAccount->id,
                'to_account_label' => $this->accountLabel($expenseAccount),
            ]);
        }

        $account = $paymentAccount ?? $expenseAccount;

        return FinanceAccountTypeTransaction::query()->create([
            'transaction_type' => 'bill_payment',
            'amount' => $amount,
            'transaction_date' => $paymentDate,
            'particular' => $resolvedParticular,
            'reference_no' => $referenceNo ?: null,
            'remarks' => $remarks !== '' ? $remarks : 'Bill approved',
            'voucher_no' => $voucherNo,
            'account_category' => $account->category,
            'main_account_type' => (string) ($paymentFields['payment_account_type'] ?? ''),
            'account_id' => $account->id,
            'account_label' => $this->accountLabel($account),
        ]);
    }

    private function accountLabel(FinanceAccount $account): string
    {
        $code = trim((string) $account->code);
        $name = trim((string) ($account->account_name ?? $account->account_label ?? ''));

        if ($name === '') {
            $name = trim((string) ($account->account_type ?? 'Account'));
        }

        return $code !== '' ? "{$name} — {$code}" : $name;
    }

    private function assertActiveFinanceAccount(FinanceAccount $account): void
    {
        if ($account->status !== 'active') {
            throw ValidationException::withMessages([
                'account_id' => ['Selected account is not active.'],
            ]);
        }
    }

    private function postBillLedgerEntry(
        FinanceAccount $account,
        int $billEntryId,
        float $amount,
        string $entryDate,
        string $particular,
        string $voucherNo,
        string $clientName,
        ?string $paymentMethod,
        string $remarks,
        ?int $typeTransactionId = null,
        bool $isCredit = false
    ): void {
        if ($amount <= 0) {
            return;
        }

        FinanceAccountLedgerEntry::create([
            'finance_account_id' => $account->id,
            'finance_bill_entry_id' => $billEntryId,
            'finance_account_type_transaction_id' => $typeTransactionId,
            'entry_date' => $entryDate,
            'particular' => $particular,
            'voucher_no' => $voucherNo,
            'client_name' => $clientName,
            'dr_amount' => $isCredit ? 0 : $amount,
            'discount' => 0,
            'cr_amount' => $isCredit ? $amount : 0,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks,
        ]);

        $delta = $isCredit ? $amount : -$amount;
        $account->update([
            'balance' => round((float) $account->balance + $delta, 2),
        ]);
    }

    private function resolveBillPaymentMethodLabel(string $paymentMethod, array $paymentFields): ?string
    {
        return match ($paymentMethod) {
            'due' => null,
            'cash' => 'Cash',
            'bank' => 'Bank',
            'income_link' => 'Income Link',
            default => ($type = trim((string) ($paymentFields['payment_account_type'] ?? ''))) !== ''
                ? $type
                : ucfirst($paymentMethod),
        };
    }
}
