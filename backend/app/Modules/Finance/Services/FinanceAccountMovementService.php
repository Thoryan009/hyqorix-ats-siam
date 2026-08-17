<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinanceAccount;
use App\Modules\Finance\Models\FinanceAccountLedgerEntry;
use App\Modules\Finance\Models\FinanceAccountTransaction;
use App\Modules\Finance\Models\FinanceAccountTypeTransaction;
use App\Modules\Finance\Models\FinanceSaleCollection;
use App\Modules\Finance\Repositories\FinanceAccountLedgerRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceAccountMovementService
{
    public function __construct(
        private readonly FinanceAccountService $accountService,
        private readonly FinanceAccountLedgerRepository $ledgerRepository,
        private readonly FinanceAccountTypeTransactionService $typeTransactionService,
        private readonly FinanceIncomeCollectionService $incomeCollectionService,
    ) {}

    public function collectPayment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $paymentMethod = (string) ($data['payment_method'] ?? '');
            $payerType = (string) ($data['payer_type'] ?? '');
            $amount = round((float) ($data['amount'] ?? 0), 2);
            $transactionDate = (string) ($data['transaction_date'] ?? now()->toDateString());
            $particular = trim((string) ($data['particular'] ?? '')) ?: 'Payment Collection';
            $referenceNo = trim((string) ($data['reference_no'] ?? ''));
            $remarks = trim((string) ($data['remarks'] ?? ''));
            $clientName = trim((string) ($data['client_name'] ?? ''));
            $demandLetter = trim((string) ($data['demand_letter'] ?? ''));
            $job = trim((string) ($data['job'] ?? ''));

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Please enter a valid amount.'],
                ]);
            }

            $result = [
                'payment_method' => $paymentMethod,
                'payer_type' => $payerType,
                'amount' => $amount,
                'main_account_id' => null,
                'party_account_id' => null,
                'type_transaction_id' => null,
            ];

            $newlyBilledCandidateSale = 0.0;
            $billsReceivableSettleAmount = 0.0;

            if (in_array($paymentMethod, ['cash', 'bank', 'balance', 'expense_link', 'adjustment'], true)) {
                $billsReceivableSettleAmount = $this->resolveBillsReceivableSettleAmount($data, $amount);
            }

            $voucherNo = $referenceNo;
            $methodLabel = match ($paymentMethod) {
                'cash' => 'Cash',
                'bank' => 'Bank',
                'due' => 'Due',
                'balance' => 'Adjust from Balance',
                'expense_link' => 'Expense Link',
                'adjustment' => 'Adjustment',
                default => ucfirst($paymentMethod),
            };

            if (in_array($paymentMethod, ['cash', 'bank', 'adjustment'], true)) {
                $isAdjustment = $paymentMethod === 'adjustment';
                if ($isAdjustment) {
                    $destinationAccountId = (int) ($data['liability_account_id'] ?? $data['main_account_id'] ?? 0);
                    if ($destinationAccountId <= 0) {
                        throw ValidationException::withMessages([
                            'liability_account_id' => ['Please select a liabilities account for adjustment.'],
                        ]);
                    }
                    $main = $this->resolveActiveAccount($destinationAccountId, 'liabilities');
                } else {
                    $mainAccountId = (int) ($data['main_account_id'] ?? 0);
                    $main = $this->resolveActiveAccount($mainAccountId, 'main');

                    $expectedType = $paymentMethod === 'bank' ? 'Bank' : 'Cash';
                    if (strcasecmp((string) $main->account_type, $expectedType) !== 0) {
                        throw ValidationException::withMessages([
                            'main_account_id' => ["Please select a {$expectedType} main account for {$methodLabel} receive method."],
                        ]);
                    }
                }

                $voucherNo = $referenceNo !== ''
                    ? $referenceNo
                    : $this->nextVoucherNo($main->id, 'SE', $transactionDate);

                $party = null;
                if (in_array($payerType, ['agent', 'client'], true)) {
                    $partyAccountId = (int) ($data['party_account_id'] ?? 0);
                    $expectedCategory = $payerType === 'client' ? 'client' : 'agent';
                    $party = $this->resolveActiveAccount($partyAccountId, $expectedCategory);
                }

                $typeTransaction = $this->createReceivePaymentTransaction(
                    $amount,
                    $transactionDate,
                    $particular,
                    $referenceNo,
                    $remarks,
                    $voucherNo,
                    $paymentMethod,
                    $main,
                    $party
                );
                $typeTransactionId = $typeTransaction?->id;

                $this->createCollectLedgerEntry([
                    'finance_account_id' => $main->id,
                    'finance_account_type_transaction_id' => $typeTransactionId,
                    'entry_date' => $transactionDate,
                    'particular' => $particular,
                    'voucher_no' => $voucherNo,
                    'demand_letter' => $demandLetter ?: null,
                    'job' => $job ?: null,
                    'client_name' => $clientName ?: null,
                    'dr_amount' => 0,
                    'cr_amount' => $amount,
                    'payment_method' => $methodLabel,
                    'remarks' => $remarks ?: null,
                ]);

                $main->update([
                    'balance' => round((float) $main->balance + $amount, 2),
                ]);

                if ($isAdjustment) {
                    $result['liability_account_id'] = $main->id;
                } else {
                    $result['main_account_id'] = $main->id;
                }
                $result['type_transaction_id'] = $typeTransactionId;

                // Agent cash/bank: per-candidate Deployment Charge (once) + payment.
                // Client cash/bank: single CR (received) without changing wallet balance.
                if ($party) {
                    if ($party->category === 'agent') {
                        $this->createAgentCandidateLedgerEntries(
                            $party,
                            $main,
                            $data,
                            $typeTransactionId,
                            $transactionDate,
                            $particular,
                            $voucherNo,
                            $demandLetter,
                            $job,
                            $clientName,
                            $remarks,
                            $methodLabel,
                            true
                        );
                    } else {
                        $this->createCollectLedgerEntry([
                            'finance_account_id' => $party->id,
                            'finance_account_type_transaction_id' => $typeTransactionId,
                            'entry_date' => $transactionDate,
                            'particular' => $particular,
                            'voucher_no' => $voucherNo,
                            'demand_letter' => $demandLetter ?: null,
                            'job' => $job ?: null,
                            'client_name' => $clientName ?: $this->accountLabel($main),
                            'dr_amount' => 0,
                            'cr_amount' => $amount,
                            'payment_method' => $methodLabel,
                            'remarks' => $remarks ?: null,
                        ]);
                    }

                    $result['party_account_id'] = $party->id;
                }

                // Candidate cash/bank: each applicant account gets its own bill + payment rows.
                if ($payerType === 'candidate') {
                    $newlyBilledCandidateSale = $this->createApplicantCandidateLedgerEntries(
                        $main,
                        $data,
                        $typeTransactionId,
                        $transactionDate,
                        $particular,
                        $voucherNo,
                        $demandLetter,
                        $job,
                        $clientName,
                        $remarks,
                        $methodLabel,
                        true
                    );
                }
            } elseif ($paymentMethod === 'due') {
                $party = null;
                if (in_array($payerType, ['agent', 'client'], true)) {
                    $partyAccountId = (int) ($data['party_account_id'] ?? 0);
                    $expectedCategory = $payerType === 'client' ? 'client' : 'agent';
                    $party = $this->resolveActiveAccount($partyAccountId, $expectedCategory);
                }

                $voucherAccountId = $party?->id ?: (int) (($data['candidates'][0]['applicant_account_id'] ?? 0));
                $voucherNo = $referenceNo !== ''
                    ? $referenceNo
                    : ($voucherAccountId > 0
                        ? $this->nextVoucherNo($voucherAccountId, 'SE', $transactionDate)
                        : sprintf('SE-DUE/%s', now()->format('y')));

                $typeTransaction = $this->createReceivePaymentTransaction(
                    $amount,
                    $transactionDate,
                    $particular,
                    $referenceNo,
                    $remarks,
                    $voucherNo,
                    $paymentMethod,
                    null,
                    $party
                );
                $typeTransactionId = $typeTransaction?->id;
                $result['type_transaction_id'] = $typeTransactionId;

                if ($party) {
                    if ($party->category === 'agent') {
                        $this->createAgentCandidateLedgerEntries(
                            $party,
                            $party,
                            $data,
                            $typeTransactionId,
                            $transactionDate,
                            $particular,
                            $voucherNo,
                            $demandLetter,
                            $job,
                            $clientName,
                            $remarks,
                            $methodLabel,
                            false
                        );
                    } else {
                        $this->createCollectLedgerEntry([
                            'finance_account_id' => $party->id,
                            'finance_account_type_transaction_id' => $typeTransactionId,
                            'entry_date' => $transactionDate,
                            'particular' => $particular !== '' ? $particular : 'Due bill',
                            'voucher_no' => $voucherNo,
                            'demand_letter' => $demandLetter ?: null,
                            'job' => $job ?: null,
                            'client_name' => $clientName ?: null,
                            'dr_amount' => $amount,
                            'cr_amount' => 0,
                            'payment_method' => null,
                            'remarks' => $remarks ?: 'Due receivable',
                        ]);
                    }

                    $result['party_account_id'] = $party->id;
                }

                if ($payerType === 'candidate') {
                    $newlyBilledCandidateSale = $this->createApplicantCandidateLedgerEntries(
                        null,
                        $data,
                        $typeTransactionId,
                        $transactionDate,
                        $particular,
                        $voucherNo,
                        $demandLetter,
                        $job,
                        $clientName,
                        $remarks,
                        $methodLabel,
                        false
                    );
                }
            } elseif ($paymentMethod === 'balance') {
                $partyAccountId = (int) ($data['party_account_id'] ?? 0);
                $expectedCategory = $payerType === 'client' ? 'client' : 'agent';
                $party = $this->resolveActiveAccount($partyAccountId, $expectedCategory);

                // Agent adjust-from-balance may go negative; clients still require sufficient funds.
                if ($expectedCategory !== 'agent' && (float) $party->balance < $amount) {
                    throw ValidationException::withMessages([
                        'amount' => ["Insufficient balance in {$this->accountLabel($party)}."],
                    ]);
                }

                $voucherNo = $referenceNo !== ''
                    ? $referenceNo
                    : $this->nextVoucherNo($party->id, 'SE', $transactionDate);

                $typeTransaction = $this->createReceivePaymentTransaction(
                    $amount,
                    $transactionDate,
                    $particular,
                    $referenceNo,
                    $remarks,
                    $voucherNo,
                    $paymentMethod,
                    null,
                    $party
                );
                $typeTransactionId = $typeTransaction?->id;

                $this->createCollectLedgerEntry([
                    'finance_account_id' => $party->id,
                    'finance_account_type_transaction_id' => $typeTransactionId,
                    'entry_date' => $transactionDate,
                    'particular' => $particular,
                    'voucher_no' => $voucherNo,
                    'demand_letter' => $demandLetter ?: null,
                    'job' => $job ?: null,
                    'client_name' => $clientName ?: null,
                    'dr_amount' => $amount,
                    'cr_amount' => 0,
                    'payment_method' => 'Adjust from Balance',
                    'remarks' => $remarks ?: null,
                ]);

                $party->update([
                    'balance' => round((float) $party->balance - $amount, 2),
                ]);

                $result['party_account_id'] = $party->id;
                $result['type_transaction_id'] = $typeTransactionId;
            } elseif ($paymentMethod === 'expense_link') {
                $expenseAccount = $this->accountService->resolveBillsReceivableLinkedExpenseAccount();

                $party = null;
                if (in_array($payerType, ['agent', 'client'], true)) {
                    $partyAccountId = (int) ($data['party_account_id'] ?? 0);
                    $expectedCategory = $payerType === 'client' ? 'client' : 'agent';
                    $party = $this->resolveActiveAccount($partyAccountId, $expectedCategory);
                }

                $voucherAccountId = $expenseAccount->id;
                $voucherNo = $referenceNo !== ''
                    ? $referenceNo
                    : $this->nextVoucherNo($voucherAccountId, 'SE', $transactionDate);

                $typeTransaction = $this->createExpenseLinkReceiveTransaction(
                    $amount,
                    $transactionDate,
                    $particular,
                    $referenceNo,
                    $remarks,
                    $voucherNo,
                    $expenseAccount,
                    $party
                );
                $typeTransactionId = $typeTransaction?->id;
                $result['type_transaction_id'] = $typeTransactionId;
                $result['expense_account_id'] = $expenseAccount->id;

                // Linked expense head ledger: DR (write-off / settle without cash).
                $this->createCollectLedgerEntry([
                    'finance_account_id' => $expenseAccount->id,
                    'finance_account_type_transaction_id' => $typeTransactionId,
                    'entry_date' => $transactionDate,
                    'particular' => $particular,
                    'voucher_no' => $voucherNo,
                    'demand_letter' => $demandLetter ?: null,
                    'job' => $job ?: null,
                    'client_name' => $clientName ?: ($party ? $this->accountLabel($party) : null),
                    'dr_amount' => $amount,
                    'cr_amount' => 0,
                    'payment_method' => $methodLabel,
                    'remarks' => $remarks ?: 'Receivable settled via expense link',
                ]);

                // Bill / party ledger: CR to settle the receivable.
                if ($party) {
                    if ($party->category === 'agent') {
                        $this->createAgentCandidateLedgerEntries(
                            $party,
                            $expenseAccount,
                            $data,
                            $typeTransactionId,
                            $transactionDate,
                            $particular,
                            $voucherNo,
                            $demandLetter,
                            $job,
                            $clientName,
                            $remarks,
                            $methodLabel,
                            true
                        );
                    } else {
                        $this->createCollectLedgerEntry([
                            'finance_account_id' => $party->id,
                            'finance_account_type_transaction_id' => $typeTransactionId,
                            'entry_date' => $transactionDate,
                            'particular' => $particular,
                            'voucher_no' => $voucherNo,
                            'demand_letter' => $demandLetter ?: null,
                            'job' => $job ?: null,
                            'client_name' => $clientName ?: $this->accountLabel($expenseAccount),
                            'dr_amount' => 0,
                            'cr_amount' => $amount,
                            'payment_method' => $methodLabel,
                            'remarks' => $remarks ?: null,
                        ]);
                    }

                    $result['party_account_id'] = $party->id;
                }

                if ($payerType === 'candidate') {
                    $newlyBilledCandidateSale = $this->createApplicantCandidateLedgerEntries(
                        $expenseAccount,
                        $data,
                        $typeTransactionId,
                        $transactionDate,
                        $particular,
                        $voucherNo,
                        $demandLetter,
                        $job,
                        $clientName,
                        $remarks,
                        $methodLabel,
                        true
                    );
                }
            } else {
                throw ValidationException::withMessages([
                    'payment_method' => ['Invalid payment method.'],
                ]);
            }

            // Capture billed sale prices before cash rows are stored (already-billed lookup).
            $newlyBilledSale = $this->sumNewlyBilledSalePrices($data);

            $this->storeSaleCollections(
                $data,
                $payerType,
                $paymentMethod,
                $transactionDate,
                $voucherNo,
                $result['type_transaction_id'] ?? null,
                $remarks
            );

            // Sale income ledger (accrual): credit the billed sale price, not the cash received.
            // Partial cash (sale 100 / receive 50) must still show Sale 100 on Trial Balance.
            $saleIncomeAmount = 0.0;
            if ($payerType === 'candidate') {
                $saleIncomeAmount = $newlyBilledCandidateSale > 0
                    ? $newlyBilledCandidateSale
                    : $newlyBilledSale;
            } elseif ($paymentMethod === 'due' && in_array($payerType, ['agent', 'client'], true)) {
                $saleIncomeAmount = $newlyBilledSale > 0 ? $newlyBilledSale : $amount;
            } elseif (
                in_array($paymentMethod, ['cash', 'bank', 'balance', 'expense_link', 'adjustment'], true)
                && $billsReceivableSettleAmount <= 0
            ) {
                $saleIncomeAmount = $newlyBilledSale > 0 ? $newlyBilledSale : $amount;
            }

            if ($saleIncomeAmount > 0) {
                $this->accountService->recordSaleIncomeEntry(
                    $saleIncomeAmount,
                    $transactionDate,
                    $voucherNo,
                    $result['type_transaction_id'] ?? null,
                    'Sale',
                    $methodLabel,
                    $remarks,
                    $clientName,
                    $demandLetter,
                    $job
                );
            }

            // Bills Receivable Ledger: DR on due / remainder; CR on settle.
            if ($paymentMethod === 'due') {
                $this->accountService->recordBillsReceivableEntry(
                    $amount,
                    'dr',
                    $transactionDate,
                    $voucherNo,
                    $result['type_transaction_id'] ?? null,
                    'Bills Receivable — Due',
                    $methodLabel,
                    $remarks,
                    $clientName,
                    $demandLetter,
                    $job
                );
            } elseif (
                in_array($paymentMethod, ['cash', 'bank', 'balance', 'expense_link', 'adjustment'], true)
                && $billsReceivableSettleAmount > 0
            ) {
                $this->accountService->recordBillsReceivableEntry(
                    $billsReceivableSettleAmount,
                    'cr',
                    $transactionDate,
                    $voucherNo,
                    $result['type_transaction_id'] ?? null,
                    'Bills Receivable — Received',
                    $methodLabel,
                    $remarks,
                    $clientName,
                    $demandLetter,
                    $job
                );
            }

            // Partial cash/bank/balance/expense-link/adjustment leaves remaining on Bills Receivable
            // and removes the candidate from Applicant/Agent Due Bills.
            $movedToReceivable = 0;
            if (in_array($paymentMethod, ['cash', 'bank', 'balance', 'expense_link', 'adjustment'], true)) {
                $movedToReceivable = $this->createDueRemainderSaleCollections(
                    $data,
                    $payerType,
                    $transactionDate,
                    $voucherNo,
                    $result['type_transaction_id'] ?? null,
                    $remarks
                );
            }
            $result['moved_to_receivable'] = $movedToReceivable;

            $this->accountService->flushCache();
            $this->typeTransactionService->flushCache();

            return $result;
        });
    }

    /**
     * @param  list<int>  $applicationIds
     * @return list<array{application_id: int, sale_price: float, collected_amount: float, has_due: bool, receivable_remaining: float}>
     */
    public function getSaleCollectionSummary(array $applicationIds): array
    {
        if ($applicationIds === []) {
            return [];
        }

        $rows = FinanceSaleCollection::query()
            ->whereIn('application_id', $applicationIds)
            ->orderByDesc('id')
            ->get(['application_id', 'sale_price', 'amount', 'payment_method']);

        $summary = [];

        foreach ($rows as $row) {
            $applicationId = (int) $row->application_id;
            $method = strtolower((string) ($row->payment_method ?? 'cash'));

            if (!isset($summary[$applicationId])) {
                $summary[$applicationId] = [
                    'application_id' => $applicationId,
                    // Latest adjusted sale price used for this candidate.
                    'sale_price' => round((float) $row->sale_price, 2),
                    'collected_amount' => 0.0,
                    'has_due' => false,
                ];
            }

            // Due records create receivable only — do not treat as cash/bank collected.
            if ($method === 'due') {
                $summary[$applicationId]['has_due'] = true;
                continue;
            }

            $summary[$applicationId]['collected_amount'] = round(
                (float) $summary[$applicationId]['collected_amount'] + (float) $row->amount,
                2
            );
        }

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
     * Receipt-list rows: sale collections grouped into payment entries.
     *
     * @param  array{page?: int, per_page?: int}  $filters
     * @return array{rows: list<array<string, mixed>>, meta: array<string, mixed>, summary: array<string, mixed>}
     */
    public function listSaleCollections(array $filters = []): array
    {
        $entries = $this->buildSaleCollectionEntries();

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));
        $total = count($entries);
        $lastPage = max(1, (int) ceil($total / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $offset = ($page - 1) * $perPage;
        $rows = array_values(array_slice($entries, $offset, $perPage));

        $now = now();
        $thisMonthCount = 0;
        $totalCollected = 0.0;

        foreach ($entries as $entry) {
            $totalCollected += (float) ($entry['total_amount'] ?? 0);
            $entryDate = (string) ($entry['entry_date'] ?? '');
            if ($entryDate !== '') {
                try {
                    $date = \Carbon\Carbon::parse($entryDate);
                    if ((int) $date->month === (int) $now->month && (int) $date->year === (int) $now->year) {
                        $thisMonthCount++;
                    }
                } catch (\Throwable) {
                    // Ignore invalid dates in summary.
                }
            }
        }

        $from = $total === 0 ? 0 : $offset + 1;
        $to = $total === 0 ? 0 : min($offset + count($rows), $total);

        $links = [];
        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'label' => (string) $i,
                'active' => $i === $page,
                'url' => $i === $page ? null : '#',
            ];
        }

        return [
            'rows' => $rows,
            'meta' => [
                'total' => $total,
                'from' => $from,
                'to' => $to,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => $lastPage,
                'links' => $links,
            ],
            'summary' => [
                'total_count' => $total,
                'this_month_count' => $thisMonthCount,
                'total_collected' => round($totalCollected, 2),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildSaleCollectionEntries(): array
    {
        $rows = FinanceSaleCollection::query()
            ->orderByDesc('id')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $typeTxnIds = $rows
            ->pluck('finance_account_type_transaction_id')
            ->filter()
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $typeTxns = $typeTxnIds === []
            ? collect()
            : FinanceAccountTypeTransaction::query()
                ->whereIn('id', $typeTxnIds)
                ->get()
                ->keyBy('id');

        $accountIds = $typeTxns
            ->flatMap(static fn ($txn) => [
                (int) ($txn->from_account_id ?? 0),
                (int) ($txn->to_account_id ?? 0),
                (int) ($txn->account_id ?? 0),
            ])
            ->filter(static fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $accounts = $accountIds === []
            ? collect()
            : FinanceAccount::query()
                ->whereIn('id', $accountIds)
                ->get()
                ->keyBy('id');

        $groups = [];

        foreach ($rows as $row) {
            $typeTxnId = (int) ($row->finance_account_type_transaction_id ?? 0);
            $entryNo = trim((string) ($row->entry_no ?? ''));
            $date = optional($row->collection_date)?->format('Y-m-d') ?: '';
            $method = strtolower((string) ($row->payment_method ?? 'cash'));

            if ($typeTxnId > 0) {
                $groupKey = "txn:{$typeTxnId}";
            } elseif ($entryNo !== '') {
                $groupKey = "entry:{$entryNo}|{$date}|{$method}";
            } else {
                $groupKey = "row:{$row->id}";
            }

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
            }

            $groups[$groupKey][] = $row;
        }

        $entries = [];

        foreach ($groups as $groupRows) {
            $methods = collect($groupRows)
                ->map(static fn ($row) => strtolower((string) ($row->payment_method ?? 'cash')))
                ->unique()
                ->values();

            $hasNonDue = $methods->contains(static fn ($method) => $method !== 'due');

            // Partial-receive remainder rows share the cash/bank transaction — omit them
            // from the receipt so the list shows the actual payment only.
            $receiptRows = $hasNonDue
                ? array_values(array_filter(
                    $groupRows,
                    static fn ($row) => strtolower((string) ($row->payment_method ?? 'cash')) !== 'due'
                ))
                : $groupRows;

            if ($receiptRows === []) {
                continue;
            }

            $first = $receiptRows[0];
            $typeTxn = $first->finance_account_type_transaction_id
                ? $typeTxns->get((int) $first->finance_account_type_transaction_id)
                : null;

            $payerType = strtolower((string) ($first->payer_type ?? 'agent'));
            $paymentMethod = strtolower((string) ($first->payment_method ?? 'cash'));

            $partyAccountId = (int) (
                $typeTxn?->from_account_id
                ?: ($payerType === 'candidate' ? 0 : ($typeTxn?->account_id ?? 0))
            );
            $partyAccount = $partyAccountId > 0 ? $accounts->get($partyAccountId) : null;

            $mainAccountId = 0;
            $mainAccountName = '';
            if (in_array($paymentMethod, ['cash', 'bank'], true)) {
                $mainAccountId = (int) (
                    $typeTxn?->to_account_id
                    ?: $typeTxn?->account_id
                    ?: 0
                );
                $mainAccount = $mainAccountId > 0 ? $accounts->get($mainAccountId) : null;
                $mainAccountName = $mainAccount
                    ? $this->accountLabel($mainAccount)
                    : (string) (
                        $typeTxn?->to_account_label
                        ?: $typeTxn?->account_label
                        ?: ''
                    );
            }

            $agentAccountId = null;
            $agentCode = '';
            $agentName = '';
            $payerId = null;
            $payerCode = '';
            $payerName = '';

            if ($partyAccount && $partyAccount->category === 'agent') {
                $agentAccountId = (int) $partyAccount->id;
                $agentCode = (string) ($partyAccount->code ?? '');
                $agentName = (string) ($partyAccount->account_name ?? '');
            }

            if ($payerType === 'agent' && $partyAccount) {
                $payerId = (int) $partyAccount->id;
                $payerCode = (string) ($partyAccount->code ?? '');
                $payerName = (string) ($partyAccount->account_name ?? '');
            } elseif ($payerType === 'client' && $partyAccount) {
                $payerId = (int) $partyAccount->id;
                $payerCode = (string) ($partyAccount->code ?? '');
                $payerName = (string) ($partyAccount->account_name ?? '');
            } elseif ($payerType === 'candidate') {
                $payerName = collect($receiptRows)
                    ->map(static fn ($row) => trim((string) ($row->candidate_name ?? '')))
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(', ');
            }

            $candidates = [];
            $totalAmount = 0.0;

            foreach ($receiptRows as $row) {
                $amount = round((float) $row->amount, 2);
                $totalAmount += $amount;
                $candidates[] = [
                    'candidate_id' => (int) $row->application_id,
                    'passport_no' => (string) ($row->passport_no ?? ''),
                    'candidate_name' => (string) ($row->candidate_name ?? ''),
                    'amount' => $amount,
                    'sale_price' => round((float) $row->sale_price, 2),
                ];
            }

            $entries[] = [
                'id' => (int) $first->id,
                'entry_no' => (string) ($first->entry_no ?: $first->voucher_no ?: ''),
                'entry_date' => optional($first->collection_date)?->format('Y-m-d'),
                'payer_type' => $payerType !== '' ? $payerType : 'agent',
                'payer_id' => $payerId,
                'payer_code' => $payerCode,
                'payer_name' => $payerName,
                'agent_account_id' => $agentAccountId,
                'agent_code' => $agentCode,
                'agent_name' => $agentName,
                'job_id' => $first->job_list_id ? (int) $first->job_list_id : null,
                'job_code' => (string) ($first->job_code ?? ''),
                'job_title' => (string) ($first->job_title ?? ''),
                'payment_method' => $paymentMethod !== '' ? $paymentMethod : 'cash',
                'main_account_id' => $mainAccountId > 0 ? $mainAccountId : null,
                'main_account_name' => $mainAccountName,
                'reference_no' => (string) ($typeTxn?->reference_no ?? $first->voucher_no ?? ''),
                'particular' => (string) ($typeTxn?->particular ?? ''),
                'remarks' => (string) ($first->remarks ?? ''),
                'total_amount' => round($totalAmount, 2),
                'candidates' => $candidates,
                'created_at' => optional($first->created_at)?->format('d/m/Y H:i:s'),
            ];
        }

        usort($entries, static function (array $a, array $b): int {
            return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
        });

        return $entries;
    }

    /**
     * Outstanding due receivables for Bills Receivable (sale collections with due method).
     *
     * @return list<array<string, mixed>>
     */
    public function listBillsReceivable(): array
    {
        $this->accountService->ensureBillsReceivableAccount(true);

        $dueRows = FinanceSaleCollection::query()
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->orderByDesc('id')
            ->get();

        $items = [];

        if ($dueRows->isNotEmpty()) {
            $applicationIds = $dueRows
                ->pluck('application_id')
                ->map(static fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $summaryById = [];
            foreach ($this->getSaleCollectionSummary($applicationIds) as $row) {
                $summaryById[(int) $row['application_id']] = $row;
            }

            $typeTxnIds = $dueRows
                ->pluck('finance_account_type_transaction_id')
                ->filter()
                ->map(static fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $typeTxns = $typeTxnIds === []
                ? collect()
                : FinanceAccountTypeTransaction::query()
                    ->whereIn('id', $typeTxnIds)
                    ->get()
                    ->keyBy('id');

            $seen = [];

            foreach ($dueRows as $dueRow) {
                $applicationId = (int) $dueRow->application_id;
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
                $typeTxn = $dueRow->finance_account_type_transaction_id
                    ? $typeTxns->get((int) $dueRow->finance_account_type_transaction_id)
                    : null;

                $partyAccountId = (int) (
                    $typeTxn?->from_account_id
                    ?: $typeTxn?->account_id
                    ?: 0
                );
                $partyAccountLabel = (string) (
                    $typeTxn?->from_account_label
                    ?: $typeTxn?->account_label
                    ?: ''
                );

                if ($partyAccountId <= 0 && strtolower((string) ($dueRow->payer_type ?? '')) === 'candidate') {
                    $applicant = FinanceAccount::query()
                        ->where('category', 'applicant')
                        ->where('entity_id', $applicationId)
                        ->where('status', 'Active')
                        ->first();
                    if ($applicant) {
                        $partyAccountId = (int) $applicant->id;
                        $partyAccountLabel = $this->accountLabel($applicant);
                    }
                }

                $salePrice = round((float) ($summary['sale_price'] ?? $dueRow->sale_price), 2);
                $collected = round((float) ($summary['collected_amount'] ?? 0), 2);

                $items[] = [
                    'id' => $applicationId,
                    'application_id' => $applicationId,
                    'source' => 'gross_income',
                    'candidate_name' => $dueRow->candidate_name,
                    'passport_no' => $dueRow->passport_no,
                    'sale_price' => $salePrice,
                    'collected_amount' => $collected,
                    'receivable_remaining' => $remaining,
                    'paid_amount' => $collected,
                    'amount' => $salePrice,
                    'payer_type' => $dueRow->payer_type,
                    'party_account_id' => $partyAccountId > 0 ? $partyAccountId : null,
                    'party_account_label' => $partyAccountLabel !== '' ? $partyAccountLabel : null,
                    'job_list_id' => $dueRow->job_list_id ? (int) $dueRow->job_list_id : null,
                    'job_code' => $dueRow->job_code,
                    'job_title' => $dueRow->job_title,
                    'collection_date' => optional($dueRow->collection_date)?->format('Y-m-d'),
                    'entry_no' => $dueRow->entry_no,
                    'voucher_no' => $dueRow->voucher_no,
                    'remarks' => $dueRow->remarks,
                    'payment_method' => 'due',
                ];
            }
        }

        return array_merge(
            $items,
            $this->incomeCollectionService->listDueReceivables(),
            $this->incomeCollectionService->listSimpleDueReceivables()
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getBillReceivable(int $applicationId): ?array
    {
        if ($applicationId < 0) {
            return $this->incomeCollectionService->getSimpleDueReceivable(-$applicationId);
        }

        if ($applicationId <= 0) {
            return null;
        }

        foreach ($this->listBillsReceivable() as $item) {
            if ((int) ($item['application_id'] ?? 0) === $applicationId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Billed sale price for candidates that do not yet have a sale-collection row.
     * Must run before storeSaleCollections().
     */
    private function sumNewlyBilledSalePrices(array $data): float
    {
        $candidates = $data['candidates'] ?? [];
        if (!is_array($candidates) || $candidates === []) {
            return 0.0;
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
        $applicationIds = array_values(array_unique($applicationIds));

        $alreadyBilledIds = $applicationIds === []
            ? []
            : FinanceSaleCollection::query()
                ->whereIn('application_id', $applicationIds)
                ->distinct()
                ->pluck('application_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        $alreadyBilledLookup = array_fill_keys($alreadyBilledIds, true);

        $total = 0.0;
        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId <= 0 || $payAmount <= 0) {
                continue;
            }
            if (isset($alreadyBilledLookup[$applicationId])) {
                continue;
            }

            $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
            $total = round($total + ($salePrice > 0 ? $salePrice : $payAmount), 2);
        }

        return $total;
    }

    private function storeSaleCollections(
        array $data,
        string $payerType,
        string $paymentMethod,
        string $transactionDate,
        string $voucherNo,
        ?int $typeTransactionId,
        string $remarks
    ): void {
        $candidates = $data['candidates'] ?? [];
        if (!is_array($candidates) || $candidates === []) {
            return;
        }

        $jobListId = isset($data['job_list_id']) ? (int) $data['job_list_id'] : null;
        $jobCode = trim((string) ($data['job_code'] ?? ''));
        $jobTitle = trim((string) ($data['job'] ?? $data['job_title'] ?? ''));
        $entryNo = trim((string) ($data['entry_no'] ?? $data['reference_no'] ?? $voucherNo));

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            $amount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId <= 0 || $amount <= 0) {
                continue;
            }

            FinanceSaleCollection::query()->create([
                'application_id' => $applicationId,
                'job_list_id' => $jobListId ?: null,
                'candidate_name' => trim((string) ($candidate['candidate_name'] ?? '')) ?: null,
                'passport_no' => trim((string) ($candidate['passport_no'] ?? '')) ?: null,
                'sale_price' => round((float) ($candidate['sale_price'] ?? 0), 2),
                'amount' => $amount,
                'payer_type' => $payerType,
                'payment_method' => $paymentMethod,
                'collection_date' => $transactionDate,
                'entry_no' => $entryNo !== '' ? $entryNo : null,
                'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
                'finance_account_type_transaction_id' => $typeTransactionId,
                'job_code' => $jobCode !== '' ? $jobCode : null,
                'job_title' => $jobTitle !== '' ? $jobTitle : null,
                'remarks' => $remarks !== '' ? $remarks : null,
            ]);
        }
    }

    /**
     * After a partial cash/bank/balance receive, create a due sale-collection
     * row for any remaining amount so it appears on Bills Receivable and is
     * hidden from Applicant/Agent Due Bills (remaining > 0 && !has_due).
     *
     * Does not post ledger entries — Deployment Charge / payment CR already
     * reflect the outstanding balance on the party/applicant ledger.
     *
     * @return int Number of applications moved to Bills Receivable
     */
    private function createDueRemainderSaleCollections(
        array $data,
        string $payerType,
        string $transactionDate,
        string $voucherNo,
        ?int $typeTransactionId,
        string $remarks
    ): int {
        $candidates = $data['candidates'] ?? [];
        if (!is_array($candidates) || $candidates === []) {
            return 0;
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
        $applicationIds = array_values(array_unique($applicationIds));
        if ($applicationIds === []) {
            return 0;
        }

        $summaryById = [];
        foreach ($this->getSaleCollectionSummary($applicationIds) as $row) {
            $summaryById[(int) $row['application_id']] = $row;
        }

        $jobListId = isset($data['job_list_id']) ? (int) $data['job_list_id'] : null;
        $jobCode = trim((string) ($data['job_code'] ?? ''));
        $jobTitle = trim((string) ($data['job'] ?? $data['job_title'] ?? ''));
        $entryNo = trim((string) ($data['entry_no'] ?? $data['reference_no'] ?? $voucherNo));
        $dueRemarks = $remarks !== ''
            ? $remarks
            : 'Partial receive — remaining moved to Bills Receivable';

        $moved = 0;

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            if ($applicationId <= 0) {
                continue;
            }

            $summary = $summaryById[$applicationId] ?? null;
            $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
            if ($salePrice <= 0) {
                $salePrice = round((float) ($summary['sale_price'] ?? 0), 2);
            }
            $collected = round((float) ($summary['collected_amount'] ?? 0), 2);
            $remaining = round(max($salePrice - $collected, 0), 2);
            if ($remaining <= 0) {
                continue;
            }

            if ((bool) ($summary['has_due'] ?? false)) {
                continue;
            }

            $hasDueRow = FinanceSaleCollection::query()
                ->where('application_id', $applicationId)
                ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
                ->exists();
            if ($hasDueRow) {
                continue;
            }

            FinanceSaleCollection::query()->create([
                'application_id' => $applicationId,
                'job_list_id' => $jobListId ?: null,
                'candidate_name' => trim((string) ($candidate['candidate_name'] ?? '')) ?: null,
                'passport_no' => trim((string) ($candidate['passport_no'] ?? '')) ?: null,
                'sale_price' => $salePrice,
                'amount' => $remaining,
                'payer_type' => $payerType,
                'payment_method' => 'due',
                'collection_date' => $transactionDate,
                'entry_no' => $entryNo !== '' ? $entryNo : null,
                'voucher_no' => $voucherNo !== '' ? $voucherNo : null,
                'finance_account_type_transaction_id' => $typeTransactionId,
                'job_code' => $jobCode !== '' ? $jobCode : null,
                'job_title' => $jobTitle !== '' ? $jobTitle : null,
                'remarks' => $dueRemarks,
            ]);

            $candidateName = trim((string) ($candidate['candidate_name'] ?? ''));
            $this->accountService->recordBillsReceivableEntry(
                $remaining,
                'dr',
                $transactionDate,
                $voucherNo !== '' ? sprintf('%s-R%d', $voucherNo, $applicationId) : "BR-{$applicationId}",
                $typeTransactionId,
                'Bills Receivable — Due Remainder',
                'Due',
                $dueRemarks,
                $candidateName,
                '',
                $jobTitle
            );

            $moved++;
        }

        return $moved;
    }

    /**
     * Cash/bank settle amount that should clear Bills Receivable (prior due exists).
     */
    private function resolveBillsReceivableSettleAmount(array $data, float $amount): float
    {
        $candidates = $data['candidates'] ?? [];
        if (!is_array($candidates) || $candidates === [] || $amount <= 0) {
            return 0.0;
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
        $applicationIds = array_values(array_unique($applicationIds));
        if ($applicationIds === []) {
            return 0.0;
        }

        $hasDue = FinanceSaleCollection::query()
            ->whereIn('application_id', $applicationIds)
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->exists();

        return $hasDue ? $amount : 0.0;
    }

    private function createReceivePaymentTransaction(
        float $amount,
        string $transactionDate,
        string $particular,
        string $referenceNo,
        string $remarks,
        string $voucherNo,
        string $paymentMethod,
        ?FinanceAccount $mainAccount,
        ?FinanceAccount $partyAccount
    ): ?FinanceAccountTypeTransaction {
        if (!$mainAccount && !$partyAccount) {
            return null;
        }

        $base = [
            'transaction_type' => 'receive_payment',
            'amount' => $amount,
            'transaction_date' => $transactionDate,
            'particular' => $particular,
            'reference_no' => $referenceNo ?: null,
            'remarks' => $remarks ?: null,
            'voucher_no' => $voucherNo,
        ];

        // Cash/Bank/Adjustment from agent/client → destination account
        if (in_array($paymentMethod, ['cash', 'bank', 'adjustment'], true) && $mainAccount && $partyAccount) {
            return FinanceAccountTypeTransaction::query()->create([
                ...$base,
                'from_account_category' => $partyAccount->category,
                'from_main_account_type' => '',
                'from_account_id' => $partyAccount->id,
                'from_account_label' => $this->accountLabel($partyAccount),
                'to_account_category' => $mainAccount->category,
                'to_main_account_type' => (string) ($mainAccount->account_type ?? ''),
                'to_account_id' => $mainAccount->id,
                'to_account_label' => $this->accountLabel($mainAccount),
            ]);
        }

        // Cash/Bank/Adjustment from candidate → destination account only
        if (in_array($paymentMethod, ['cash', 'bank', 'adjustment'], true) && $mainAccount) {
            return FinanceAccountTypeTransaction::query()->create([
                ...$base,
                'to_account_category' => $mainAccount->category,
                'to_main_account_type' => (string) ($mainAccount->account_type ?? ''),
                'to_account_id' => $mainAccount->id,
                'to_account_label' => $this->accountLabel($mainAccount),
                'account_category' => $mainAccount->category,
                'main_account_type' => (string) ($mainAccount->account_type ?? ''),
                'account_id' => $mainAccount->id,
                'account_label' => $this->accountLabel($mainAccount),
            ]);
        }

        // Due / Adjust from balance: party account only
        if ($partyAccount) {
            return FinanceAccountTypeTransaction::query()->create([
                ...$base,
                'account_category' => $partyAccount->category,
                'main_account_type' => '',
                'account_id' => $partyAccount->id,
                'account_label' => $this->accountLabel($partyAccount),
            ]);
        }

        return null;
    }

    private function createExpenseLinkReceiveTransaction(
        float $amount,
        string $transactionDate,
        string $particular,
        string $referenceNo,
        string $remarks,
        string $voucherNo,
        FinanceAccount $expenseAccount,
        ?FinanceAccount $partyAccount
    ): FinanceAccountTypeTransaction {
        $base = [
            'transaction_type' => 'receive_payment',
            'amount' => $amount,
            'transaction_date' => $transactionDate,
            'particular' => $particular,
            'reference_no' => $referenceNo ?: null,
            'remarks' => $remarks ?: null,
            'voucher_no' => $voucherNo,
            'to_account_category' => $expenseAccount->category,
            'to_account_id' => $expenseAccount->id,
            'to_account_label' => $this->accountLabel($expenseAccount),
            'account_category' => $expenseAccount->category,
            'account_id' => $expenseAccount->id,
            'account_label' => $this->accountLabel($expenseAccount),
        ];

        if ($partyAccount) {
            $base['from_account_category'] = $partyAccount->category;
            $base['from_account_id'] = $partyAccount->id;
            $base['from_account_label'] = $this->accountLabel($partyAccount);
        }

        return FinanceAccountTypeTransaction::query()->create($base);
    }

    /**
     * Post one Deployment Charge + one payment on each related applicant account.
     * Deployment Charge is created only on the first collection for that applicant.
     */
    private function createApplicantCandidateLedgerEntries(
        ?FinanceAccount $main,
        array $data,
        ?int $typeTransactionId,
        string $transactionDate,
        string $particular,
        string $voucherNo,
        string $demandLetter,
        string $job,
        string $clientName,
        string $remarks,
        string $paymentMethodLabel = 'Cash',
        bool $postPaymentCredit = true
    ): float {
        $candidates = $data['candidates'] ?? [];
        if (!is_array($candidates) || $candidates === []) {
            return 0.0;
        }

        $applicationIds = [];
        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId > 0 && $payAmount > 0) {
                $applicationIds[] = $applicationId;
            }
        }

        $applicationIds = array_values(array_unique($applicationIds));
        $alreadyBilledIds = $applicationIds === []
            ? []
            : FinanceSaleCollection::query()
                ->whereIn('application_id', $applicationIds)
                ->distinct()
                ->pluck('application_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        $alreadyBilledLookup = array_fill_keys($alreadyBilledIds, true);
        $newlyBilledSale = 0.0;

        foreach ($candidates as $index => $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId <= 0 || $payAmount <= 0) {
                continue;
            }

            $candidateName = trim((string) ($candidate['candidate_name'] ?? ''));
            $passportNo = trim((string) ($candidate['passport_no'] ?? ''));
            $candidateLabel = trim($candidateName.($passportNo !== '' ? " ({$passportNo})" : ''));
            $applicant = $this->resolveApplicantAccountByApplicationId(
                $applicationId,
                $candidateLabel !== '' ? $candidateLabel : null
            );

            $salePrice = round((float) ($candidate['sale_price'] ?? 0), 2);
            $candidateVoucherNo = $voucherNo !== ''
                ? sprintf('%s-%d', $voucherNo, $index + 1)
                : (string) $applicationId;

            if (!isset($alreadyBilledLookup[$applicationId]) && $salePrice > 0) {
                $this->createCollectLedgerEntry([
                    'finance_account_id' => $applicant->id,
                    'finance_account_type_transaction_id' => $typeTransactionId,
                    'entry_date' => $transactionDate,
                    'particular' => 'Deployment Charge',
                    'voucher_no' => $candidateVoucherNo,
                    'demand_letter' => $demandLetter ?: null,
                    'job' => $job ?: null,
                    'client_name' => $candidateLabel !== '' ? $candidateLabel : ($clientName ?: null),
                    'dr_amount' => $salePrice,
                    'cr_amount' => 0,
                    'payment_method' => null,
                    'remarks' => $candidateLabel !== ''
                        ? "Bill for {$candidateLabel}"
                        : 'Bill generated',
                ]);
                $newlyBilledSale = round($newlyBilledSale + $salePrice, 2);
            }

            if (!$postPaymentCredit) {
                continue;
            }

            $this->createCollectLedgerEntry([
                'finance_account_id' => $applicant->id,
                'finance_account_type_transaction_id' => $typeTransactionId,
                'entry_date' => $transactionDate,
                'particular' => $particular,
                'voucher_no' => $candidateVoucherNo,
                'demand_letter' => $demandLetter ?: null,
                'job' => $job ?: null,
                'client_name' => $candidateLabel !== ''
                    ? $candidateLabel
                    : ($clientName ?: ($main ? $this->accountLabel($main) : null)),
                'dr_amount' => 0,
                'cr_amount' => $payAmount,
                'payment_method' => $paymentMethodLabel,
                'remarks' => $remarks !== ''
                    ? $remarks
                    : ($candidateLabel !== '' ? "Payment collection for {$candidateLabel}" : null),
            ]);
        }

        return $newlyBilledSale;
    }

    private function resolveApplicantAccountByApplicationId(
        int $applicationId,
        ?string $candidateLabel = null
    ): FinanceAccount {
        $account = FinanceAccount::query()
            ->where('category', 'applicant')
            ->where('entity_id', $applicationId)
            ->lockForUpdate()
            ->first();

        $label = $candidateLabel ?: ("application #{$applicationId}");

        if (!$account) {
            throw ValidationException::withMessages([
                'candidates' => [
                    "No applicant account found for {$label}. Please create the applicant account first.",
                ],
            ]);
        }

        if ($account->status !== 'active') {
            throw ValidationException::withMessages([
                'candidates' => [
                    "Applicant account for {$label} is not active.",
                ],
            ]);
        }

        return $account;
    }

    /**
     * Post one Deployment Charge + one payment per candidate.
     * Deployment Charge is created only on the first collection for that candidate.
     */
    private function createAgentCandidateLedgerEntries(
        FinanceAccount $party,
        FinanceAccount $main,
        array $data,
        ?int $typeTransactionId,
        string $transactionDate,
        string $particular,
        string $voucherNo,
        string $demandLetter,
        string $job,
        string $clientName,
        string $remarks,
        string $paymentMethodLabel = 'Cash',
        bool $postPaymentCredit = true
    ): void {
        $candidates = $data['candidates'] ?? [];
        if (!is_array($candidates) || $candidates === []) {
            return;
        }

        $applicationIds = [];
        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
            $payAmount = round((float) ($candidate['amount'] ?? 0), 2);
            if ($applicationId > 0 && $payAmount > 0) {
                $applicationIds[] = $applicationId;
            }
        }

        $applicationIds = array_values(array_unique($applicationIds));
        $alreadyBilledIds = $applicationIds === []
            ? []
            : FinanceSaleCollection::query()
                ->whereIn('application_id', $applicationIds)
                ->distinct()
                ->pluck('application_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        $alreadyBilledLookup = array_fill_keys($alreadyBilledIds, true);

        foreach ($candidates as $index => $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            $applicationId = (int) ($candidate['application_id'] ?? $candidate['candidate_id'] ?? 0);
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

            // First collection only: individual Deployment Charge for this candidate.
            if (!isset($alreadyBilledLookup[$applicationId]) && $salePrice > 0) {
                $this->createCollectLedgerEntry([
                    'finance_account_id' => $party->id,
                    'finance_account_type_transaction_id' => $typeTransactionId,
                    'entry_date' => $transactionDate,
                    'particular' => 'Deployment Charge',
                    'voucher_no' => $candidateVoucherNo,
                    'demand_letter' => $demandLetter ?: null,
                    'job' => $job ?: null,
                    'client_name' => $candidateLabel !== '' ? $candidateLabel : ($clientName ?: null),
                    'dr_amount' => $salePrice,
                    'cr_amount' => 0,
                    'payment_method' => null,
                    'remarks' => $candidateLabel !== ''
                        ? "Bill for {$candidateLabel}"
                        : 'Bill generated',
                ]);
            }

            // Always post this candidate's cash/bank payment (including due follow-ups).
            if (!$postPaymentCredit) {
                continue;
            }

            $this->createCollectLedgerEntry([
                'finance_account_id' => $party->id,
                'finance_account_type_transaction_id' => $typeTransactionId,
                'entry_date' => $transactionDate,
                'particular' => $particular,
                'voucher_no' => $candidateVoucherNo,
                'demand_letter' => $demandLetter ?: null,
                'job' => $job ?: null,
                'client_name' => $candidateLabel !== ''
                    ? $candidateLabel
                    : ($clientName ?: $this->accountLabel($main)),
                'dr_amount' => 0,
                'cr_amount' => $payAmount,
                'payment_method' => $paymentMethodLabel,
                'remarks' => $remarks !== ''
                    ? $remarks
                    : ($candidateLabel !== '' ? "Payment collection for {$candidateLabel}" : null),
            ]);
        }
    }

    private function createCollectLedgerEntry(array $attributes): void
    {
        FinanceAccountLedgerEntry::create([
            'discount' => 0,
            ...$attributes,
        ]);
    }

    private function resolveActiveAccount(int $accountId, ?string $expectedCategory = null): FinanceAccount
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

        if ($expectedCategory !== null && $account->category !== $expectedCategory) {
            throw ValidationException::withMessages([
                'account_id' => ['Selected account does not belong to the expected category.'],
            ]);
        }

        return $account;
    }

    public function transfer(array $data): FinanceAccountTransaction
    {
        return $this->processMovement('transfer', $data, function (FinanceAccount $from, FinanceAccount $to) {
            if ($from->account_type !== 'bank' || $to->account_type !== 'bank') {
                throw ValidationException::withMessages([
                    'account_type' => 'Transfers are only allowed between bank accounts.',
                ]);
            }
        }, 'TR', 'Account Transfer', 'Transfer');
    }

    public function deposit(array $data): FinanceAccountTransaction
    {
        return $this->processMovement('deposit', $data, function (FinanceAccount $from, FinanceAccount $to) {
            if (
                strcasecmp((string) $from->account_type, 'cash') !== 0
                || strcasecmp((string) $to->account_type, 'bank') !== 0
            ) {
                throw ValidationException::withMessages([
                    'account_type' => 'Deposit must be from a cash account to a bank account.',
                ]);
            }
        }, 'RC', 'Bank Deposit', 'Transfer');
    }

    public function withdraw(array $data): FinanceAccountTransaction
    {
        return $this->processMovement('withdraw', $data, function (FinanceAccount $from, FinanceAccount $to) {
            if (
                strcasecmp((string) $from->account_type, 'bank') !== 0
                || strcasecmp((string) $to->account_type, 'cash') !== 0
            ) {
                throw ValidationException::withMessages([
                    'account_type' => 'Withdraw must be from a bank account to a cash account.',
                ]);
            }
        }, 'PY', 'Bank Withdraw', 'Transfer');
    }

    public function getLedgerEntries(int $accountId, array $filters = [])
    {
        $account = FinanceAccount::query()->findOrFail($accountId);
        $this->ensureOpeningBalanceEntry($account);

        return $this->ledgerRepository->getPaginatedData(array_merge($filters, [
            'finance_account_id' => $accountId,
        ]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getLedgerRowsForExport(int $accountId, array $filters): array
    {
        $account = FinanceAccount::query()->findOrFail($accountId);
        $this->ensureOpeningBalanceEntry($account);

        $entries = $this->ledgerRepository->getAllData(array_merge($filters, [
            'finance_account_id' => $accountId,
        ]));

        $balance = 0.0;
        $rows = [];

        foreach ($entries as $entry) {
            $dr = (float) ($entry->dr_amount ?? 0);
            $discount = (float) ($entry->discount ?? 0);
            $cr = (float) ($entry->cr_amount ?? 0);

            if ($dr > 0) {
                $balance -= max($dr - $discount, 0);
            } elseif ($cr > 0) {
                $balance += $cr + $discount;
            }

            $rows[] = [
                'id' => (int) $entry->id,
                'date' => $entry->entry_date?->format('Y-m-d') ?? $entry->entry_date?->toDateString() ?? '',
                'particular' => (string) ($entry->particular ?? ''),
                'voucher_no' => (string) ($entry->voucher_no ?? ''),
                'demand_letter' => (string) ($entry->demand_letter ?? ''),
                'job' => (string) ($entry->job ?? ''),
                'client_name' => (string) ($entry->client_name ?? ''),
                'dr_amount' => $dr,
                'discount' => $discount,
                'cr_amount' => $cr,
                'payment_method' => (string) ($entry->payment_method ?? ''),
                'remarks' => (string) ($entry->remarks ?? ''),
                'balance' => $balance,
            ];
        }

        return $rows;
    }

    /**
     * @return array{useAmountLabel: bool, isSystemLedger: bool, isIncomeHeadLedger: bool, isManualAmountLedger: bool, drLabel: string, crLabel: string, amountLabel: string}
     */
    private function resolveAccountLedgerDisplayMode(string $category): array
    {
        $normalized = strtolower(trim($category));

        $isSystemLedger = in_array($normalized, ['capital', 'sale', 'bills_receivable', 'income_receivable'], true);
        $isIncomeHeadLedger = in_array($normalized, ['client_income', 'recruitment_income', 'other_income'], true);
        $isManualAmountLedger = in_array($normalized, ['asset', 'liabilities', 'owners_equity'], true);

        $useAmountLabel = $isIncomeHeadLedger || $isManualAmountLedger;

        $drLabel = ($isSystemLedger || $isManualAmountLedger)
            ? 'DR'
            : ($isIncomeHeadLedger ? 'DR (Bill)' : 'DR (Payment)');

        $crLabel = ($isSystemLedger || $isManualAmountLedger) ? 'CR' : 'CR (Received)';

        $amountLabel = $useAmountLabel ? 'Amount' : 'Balance';

        return [
            'useAmountLabel' => $useAmountLabel,
            'isSystemLedger' => $isSystemLedger,
            'isIncomeHeadLedger' => $isIncomeHeadLedger,
            'isManualAmountLedger' => $isManualAmountLedger,
            'drLabel' => $drLabel,
            'crLabel' => $crLabel,
            'amountLabel' => $amountLabel,
        ];
    }

    private function formatNumber(float $value, int $decimals = 2): string
    {
        $str = number_format($value, $decimals, '.', '');
        return rtrim(rtrim($str, '0'), '.');
    }

    private function escapeCsvValue(string $value): string
    {
        $needsQuotes = str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n");
        if (!$needsQuotes) return $value;

        return '"' . str_replace('"', '""', $value) . '"';
    }

    private function formatLedgerCellAmount(float $value): string
    {
        if ($value <= 0) return '';
        return $this->formatNumber($value, 2);
    }

    private function formatBalanceCell(float $balance): string
    {
        if (abs($balance) < 0.00001) return '';
        if ($balance < 0) {
            return $this->formatNumber(abs($balance), 2) . ' DR';
        }

        return $this->formatNumber($balance, 2) . ' CR';
    }

    public function exportAccountLedgerCsv(int $accountId, array $filters = []): string
    {
        $account = FinanceAccount::query()->findOrFail($accountId);
        $mode = $this->resolveAccountLedgerDisplayMode((string) ($account->category ?? ''));

        $rows = $this->getLedgerRowsForExport($accountId, $filters);

        $lines = [];

        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        if (!empty($account->account_name)) {
            $lines[] = 'Account,' . $this->escapeCsvValue((string) $account->account_name);
        }

        if (!empty($account->account_label)) {
            $lines[] = 'Account Label,' . $this->escapeCsvValue((string) $account->account_label);
        }

        if (!empty($fromDate) || !empty($toDate)) {
            $lines[] = 'Date Range,' . $this->escapeCsvValue(
                sprintf(
                    '%s to %s',
                    $fromDate ? (string) $fromDate : 'Start',
                    $toDate ? (string) $toDate : 'End'
                )
            );
        }

        if ($lines !== []) {
            $lines[] = '';
        }

        $lines[] = implode(',', [
            'Date',
            'Particular',
            'Voucher No',
            'Demand Letter',
            'Job',
            'Reference',
            $mode['drLabel'],
            'Discount',
            $mode['crLabel'],
            'Payment Method',
            $mode['amountLabel'],
            'Remarks',
        ]);

        foreach ($rows as $row) {
            $amountCell = $mode['useAmountLabel']
                ? $this->formatBalanceCell((float) $row['balance'])
                : $this->formatNumber((float) $row['balance'], 2);

            $cells = [
                (string) $row['date'],
                (string) $row['particular'],
                (string) $row['voucher_no'],
                (string) $row['demand_letter'],
                (string) $row['job'],
                (string) $row['client_name'],
                $this->formatLedgerCellAmount((float) $row['dr_amount']),
                $this->formatLedgerCellAmount((float) $row['discount']),
                $this->formatLedgerCellAmount((float) $row['cr_amount']),
                (string) $row['payment_method'],
                $amountCell,
                (string) $row['remarks'],
            ];

            $escaped = array_map(fn ($value) => $this->escapeCsvValue((string) $value), $cells);
            $lines[] = implode(',', $escaped);
        }

        $bom = "\xEF\xBB\xBF";
        return $bom . implode("\n", $lines);
    }

    public function exportAccountLedgerPdf(int $accountId, array $filters = []): string
    {
        $account = FinanceAccount::query()->findOrFail($accountId);
        $mode = $this->resolveAccountLedgerDisplayMode((string) ($account->category ?? ''));

        $rows = $this->getLedgerRowsForExport($accountId, $filters);

        $fromDate = $filters['from_date'] ?? '';
        $toDate = $filters['to_date'] ?? '';

        // Pre-format values for the PDF template.
        $formattedRows = array_map(function (array $row) use ($mode) {
            $dr = (float) $row['dr_amount'];
            $discount = (float) $row['discount'];
            $cr = (float) $row['cr_amount'];
            $balance = (float) $row['balance'];

            return [
                'id' => $row['id'],
                'date' => $row['date'],
                'particular' => $row['particular'],
                'voucher_no' => $row['voucher_no'],
                'demand_letter' => $row['demand_letter'],
                'job' => $row['job'],
                'client_name' => $row['client_name'],
                'dr_amount' => $dr > 0 ? $this->formatNumber($dr, 2) : '',
                'discount' => $discount > 0 ? $this->formatNumber($discount, 2) : '',
                'cr_amount' => $cr > 0 ? $this->formatNumber($cr, 2) : '',
                'payment_method' => $row['payment_method'],
                'amount' => $mode['useAmountLabel'] ? $this->formatBalanceCell($balance) : $this->formatNumber($balance, 2),
                'remarks' => $row['remarks'],
            ];
        }, $rows);

        return Pdf::loadView('finance.account-ledger-pdf', [
            'accountName' => (string) ($account->account_name ?? $account->head_name ?? ''),
            'accountLabel' => (string) ($account->account_label ?? ''),
            'category' => (string) ($account->category ?? ''),
            'fromDate' => (string) $fromDate,
            'toDate' => (string) $toDate,
            'mode' => $mode,
            'rows' => $formattedRows,
        ])->setPaper('a4', 'landscape')->output();
    }

    private function ensureOpeningBalanceEntry(FinanceAccount $account): void
    {
        $openingAmount = (float) $account->opening_balance > 0
            ? (float) $account->opening_balance
            : ((float) $account->balance > 0 && $account->ledgerEntries()->doesntExist()
                ? (float) $account->balance
                : 0);

        if ($openingAmount <= 0) {
            return;
        }

        $this->accountService->recordOpeningBalanceTransaction($account, $openingAmount);
    }

    private function processMovement(
        string $type,
        array $data,
        callable $validateTypes,
        string $voucherPrefix,
        string $defaultParticular,
        string $paymentMethod
    ): FinanceAccountTransaction {
        return DB::transaction(function () use ($type, $data, $validateTypes, $voucherPrefix, $defaultParticular, $paymentMethod) {
            $from = FinanceAccount::query()->lockForUpdate()->findOrFail($data['from_account_id']);
            $to = FinanceAccount::query()->lockForUpdate()->findOrFail($data['to_account_id']);

            $this->assertMainAccount($from);
            $this->assertMainAccount($to);
            $this->assertActiveAccount($from);
            $this->assertActiveAccount($to);

            $validateTypes($from, $to);

            $amount = round((float) $data['amount'], 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Please enter a valid amount.',
                ]);
            }

            if ((float) $from->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient balance in the source account.',
                ]);
            }

            $transactionDate = $data['transaction_date'];
            $particular = trim((string) ($data['particular'] ?? '')) ?: $defaultParticular;
            $referenceNo = trim((string) ($data['reference_no'] ?? ''));
            $remarks = trim((string) ($data['remarks'] ?? ''));

            $fromLabel = $this->accountLabel($from);
            $toLabel = $this->accountLabel($to);

            $voucherNo = $referenceNo ?: $this->nextVoucherNo($from->id, $voucherPrefix, $transactionDate);

            $transaction = FinanceAccountTransaction::create([
                'type' => $type,
                'from_account_id' => $from->id,
                'to_account_id' => $to->id,
                'amount' => $amount,
                'voucher_no' => $voucherNo,
                'particular' => $particular,
                'reference_no' => $referenceNo ?: null,
                'remarks' => $remarks ?: null,
                'transaction_date' => $transactionDate,
            ]);

            $outRemarks = $remarks ?: match ($type) {
                'transfer' => "Transfer to {$toLabel}",
                'deposit' => "Deposit from {$fromLabel} to {$toLabel}",
                'withdraw' => "Withdraw from {$fromLabel} to {$toLabel}",
                default => $particular,
            };

            $inRemarks = $remarks ?: match ($type) {
                'transfer' => "Transfer from {$fromLabel}",
                'deposit' => "Deposit from {$fromLabel}",
                'withdraw' => "Withdraw to {$toLabel}",
                default => $particular,
            };

            $fromLedger = FinanceAccountLedgerEntry::create([
                'finance_account_id' => $from->id,
                'finance_account_transaction_id' => $transaction->id,
                'entry_date' => $transactionDate,
                'particular' => $particular,
                'voucher_no' => $voucherNo,
                'client_name' => $toLabel,
                'dr_amount' => $amount,
                'discount' => 0,
                'cr_amount' => 0,
                'payment_method' => $paymentMethod,
                'remarks' => $outRemarks,
            ]);

            $toLedger = FinanceAccountLedgerEntry::create([
                'finance_account_id' => $to->id,
                'finance_account_transaction_id' => $transaction->id,
                'entry_date' => $transactionDate,
                'particular' => $particular,
                'voucher_no' => $voucherNo,
                'client_name' => $fromLabel,
                'dr_amount' => 0,
                'discount' => 0,
                'cr_amount' => $amount,
                'payment_method' => $paymentMethod,
                'remarks' => $inRemarks,
            ]);

            $typeTransaction = FinanceAccountTypeTransaction::query()->create([
                'transaction_type' => $type,
                'amount' => $amount,
                'transaction_date' => $transactionDate,
                'particular' => $particular,
                'reference_no' => $referenceNo ?: null,
                'remarks' => $remarks ?: null,
                'voucher_no' => $voucherNo,
                'from_account_category' => $from->category,
                'from_main_account_type' => (string) ($from->account_type ?? ''),
                'from_account_id' => $from->id,
                'from_account_label' => $fromLabel,
                'to_account_category' => $to->category,
                'to_main_account_type' => (string) ($to->account_type ?? ''),
                'to_account_id' => $to->id,
                'to_account_label' => $toLabel,
            ]);

            $fromLedger->update([
                'finance_account_type_transaction_id' => $typeTransaction->id,
            ]);
            $toLedger->update([
                'finance_account_type_transaction_id' => $typeTransaction->id,
            ]);

            $from->update(['balance' => round((float) $from->balance - $amount, 2)]);
            $to->update(['balance' => round((float) $to->balance + $amount, 2)]);

            $this->accountService->flushCache();

            return $transaction->load(['fromAccount', 'toAccount']);
        });
    }

    private function assertMainAccount(FinanceAccount $account): void
    {
        if ($account->category !== 'main') {
            throw ValidationException::withMessages([
                'account' => 'Only main accounts can be used for this transaction.',
            ]);
        }
    }

    private function assertActiveAccount(FinanceAccount $account): void
    {
        if ($account->status !== 'active') {
            throw ValidationException::withMessages([
                'status' => 'Both accounts must be active.',
            ]);
        }
    }

    private function accountLabel(FinanceAccount $account): string
    {
        $label = trim((string) $account->account_label);

        return $label
            ? "{$account->account_name} - {$label}"
            : (string) $account->account_name;
    }

    private function nextVoucherNo(int $accountId, string $prefix, string $date): string
    {
        $yearSuffix = substr((string) date('Y', strtotime($date)), -2);

        $count = FinanceAccountLedgerEntry::query()
            ->where('finance_account_id', $accountId)
            ->where('voucher_no', 'like', "{$prefix}-%")
            ->where('voucher_no', 'like', "%/{$yearSuffix}")
            ->count() + 1;

        return sprintf('%s-%03d/%s', $prefix, $count, $yearSuffix);
    }
}
