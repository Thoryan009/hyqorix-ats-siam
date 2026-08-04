<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinanceAccount;
use Illuminate\Support\Carbon;

class FinanceTrialBalanceService
{
    public function __construct(
        private readonly FinanceBillEntryService $billEntryService,
        private readonly FinanceAccountService $accountService,
        private readonly FinanceIncomeCollectionService $incomeCollectionService,
    ) {}

    private const CATEGORY_LABELS = [
        'main' => 'Main Accounts',
        'capital' => 'Capital Account',
        'asset' => 'Asset Accounts',
        'liabilities' => 'Liabilities Accounts',
        'agent_advanced' => 'Agent Advanced',
        'sale' => 'Sale',
        'bills_receivable' => 'Sale Receivable',
        'income_receivable' => 'Income Receivable',
        'expense_payable' => 'Expense Payable',
        'direct_expense' => 'Direct Expense Accounts',
        'client_recruitment' => 'Client Recruitment Accounts',
        'operating_expense' => 'Operating Expense Accounts',
        'recruitment_income' => 'Recruitment Income Accounts',
        'client_income' => 'Client Income Accounts',
        'other_income' => 'Operating Income Accounts',
        'agent' => 'Agent Accounts',
        'vendor' => 'Vendor Accounts',
        'principal' => 'Principal Accounts',
        'client' => 'Client Accounts',
        'staff' => 'Staff Accounts',
        'applicant' => 'Applicant Accounts',
    ];

    /**
     * Classic Final Trial Balance statement order:
     * Assets → Receivables/Payables parties → Expenses → Capital → Income
     */
    private const CATEGORY_ORDER = [
        'main',
        'asset',
        'bills_receivable',
        'income_receivable',
        'expense_payable',
        'agent_advanced',
        'staff',
        'vendor',
        'principal',
        'direct_expense',
        'client_recruitment',
        'operating_expense',
        'capital',
        'liabilities',
        'sale',
        'recruitment_income',
        'client_income',
        'other_income',
    ];

    /**
     * Ledger stores main/capital with inverted cash convention (balance = CR − DR).
     * Flip those sides so Final Trial Balance matches textbook Debit/Credit layout:
     * Cash/Bank (assets) on Debit, Owner's Capital on Credit.
     * Manual Asset accounts (e.g. Advanced Given) use normal DR-increase posting — no flip.
     */
    private const FLIP_NET_SIDE_CATEGORIES = [
        'main',
        'capital',
    ];

    /** Expense heads: TB Debit = gross charges (ignore payment-settlement CRs). */
    private const EXPENSE_CATEGORIES = [
        'direct_expense',
        'client_recruitment',
        'operating_expense',
    ];

    /** Income heads: TB Credit = gross income credits. */
    private const INCOME_CATEGORIES = [
        'sale',
        'recruitment_income',
        'client_income',
        'other_income',
    ];

    public function getReport(array $filters = []): array
    {
        $fromDate = $this->normalizeDate($filters['from_date'] ?? null);
        $toDate = $this->normalizeDate($filters['to_date'] ?? null) ?? now()->toDateString();

        // Older bill payments often hit cash/bank only (no expense accounts existed).
        // Ensure expense-head accounts + missing DR rows before building the statement.
        $this->billEntryService->backfillApprovedBillExpenseLedgers($toDate);

        // Ensure Agent Advanced consolidating ledger exists and includes prior agent advances.
        $this->accountService->ensureAgentAdvancedAccount(true);

        // Ensure Sale income ledger exists and includes prior recognized sale collections.
        $this->accountService->ensureSaleAccount(true);

        // Ensure Bills Receivable consolidating ledger exists for Sale dues.
        $this->accountService->ensureBillsReceivableAccount(true);

        // Backfill income-head receivable ledgers for prior PL Income dues.
        $this->accountService->backfillMissingIncomeReceivableEntries();

        // Backfill per-head expense payable ledgers for approved due bills.
        $this->accountService->backfillMissingExpensePayableEntries();

        // Ensure due bills missing linked expense account ids are repaired for payable queues.
        $this->billEntryService->backfillMissingBillExpenseAccountLinks();

        // Ensure collected client/operating income appears on income-head ledgers for TB.
        $this->incomeCollectionService->backfillMissingIncomeHeadCredits();

        $accounts = FinanceAccount::query()
            ->withSum([
                'ledgerEntries as total_dr' => function ($query) use ($toDate) {
                    $query->whereDate('entry_date', '<=', $toDate);
                },
            ], 'dr_amount')
            ->withSum([
                'ledgerEntries as total_cr' => function ($query) use ($toDate) {
                    $query->whereDate('entry_date', '<=', $toDate);
                },
            ], 'cr_amount')
            ->orderBy('account_name')
            ->orderBy('id')
            ->get();

        $rows = [];
        $debitTotal = 0.0;
        $creditTotal = 0.0;

        foreach ($accounts as $account) {
            $totalDr = round((float) ($account->total_dr ?? 0), 2);
            $totalCr = round((float) ($account->total_cr ?? 0), 2);
            $category = (string) ($account->category ?? 'main');

            // Party AR/AP is consolidated / represented elsewhere:
            // - agents → Agent Advanced
            // - applicants → Bills Receivable
            // - clients → Income Receivable (e.g. Client Commission Receivable)
            // - vendor / staff / principal → Asset & Liabilities accounts (Other Transaction)
            if (in_array($category, ['agent', 'applicant', 'client', 'vendor', 'staff', 'principal'], true)) {
                continue;
            }

            [$debitBalance, $creditBalance] = $this->resolvePresentationBalances(
                $category,
                $totalDr,
                $totalCr
            );

            if ($debitBalance < 0.005 && $creditBalance < 0.005) {
                continue;
            }

            $rows[] = [
                'account_id' => $account->id,
                'account_name' => $this->accountDisplayName($account),
                'account_code' => trim((string) ($account->code ?? '')),
                'account_type' => (string) ($account->account_type ?? ''),
                'category' => $category,
                'category_label' => self::CATEGORY_LABELS[$category]
                    ?? ucwords(str_replace('_', ' ', $category)),
                'total_dr' => $totalDr,
                'total_cr' => $totalCr,
                'debit_balance' => $debitBalance,
                'credit_balance' => $creditBalance,
                'sort_order' => $this->categorySortOrder($category),
            ];

            $debitTotal = round($debitTotal + $debitBalance, 2);
            $creditTotal = round($creditTotal + $creditBalance, 2);
        }

        usort($rows, function (array $a, array $b) {
            // Debit balances first, then credit balances.
            $aSide = ($a['debit_balance'] ?? 0) > 0.005 ? 0 : 1;
            $bSide = ($b['debit_balance'] ?? 0) > 0.005 ? 0 : 1;
            if ($aSide !== $bSide) {
                return $aSide <=> $bSide;
            }

            if ($a['sort_order'] !== $b['sort_order']) {
                return $a['sort_order'] <=> $b['sort_order'];
            }

            $nameCompare = strcasecmp($a['account_name'], $b['account_name']);
            if ($nameCompare !== 0) {
                return $nameCompare;
            }

            return $a['account_id'] <=> $b['account_id'];
        });

        $groups = [];
        foreach ($rows as $row) {
            $category = $row['category'];
            if (!isset($groups[$category])) {
                $groups[$category] = [
                    'category' => $category,
                    'category_label' => $row['category_label'],
                    'rows' => [],
                    'debit_total' => 0.0,
                    'credit_total' => 0.0,
                ];
            }

            $groups[$category]['rows'][] = $row;
            $groups[$category]['debit_total'] = round(
                $groups[$category]['debit_total'] + $row['debit_balance'],
                2
            );
            $groups[$category]['credit_total'] = round(
                $groups[$category]['credit_total'] + $row['credit_balance'],
                2
            );
        }

        $groupList = array_values($groups);
        $accountCount = count($rows);
        $difference = round($debitTotal - $creditTotal, 2);
        $isBalanced = abs($difference) < 0.005;

        return [
            'title' => 'Final Trial Balance',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'groups' => $groupList,
            'rows' => $rows,
            'debit_total' => $debitTotal,
            'credit_total' => $creditTotal,
            'difference' => $difference,
            'is_balanced' => $isBalanced,
            'summary' => [
                'account_count' => $accountCount,
                'group_count' => count($groupList),
                'debit_total' => $debitTotal,
                'credit_total' => $creditTotal,
                'difference' => $difference,
                'is_balanced' => $isBalanced,
            ],
        ];
    }

    /**
     * @return array{0: float, 1: float} [debit_balance, credit_balance]
     */
    private function resolvePresentationBalances(
        string $category,
        float $totalDr,
        float $totalCr,
    ): array {
        // Expense: show incurred charges on Debit (settlement CRs must not wipe TB).
        if (in_array($category, self::EXPENSE_CATEGORIES, true)) {
            return [round(max($totalDr, 0), 2), 0.0];
        }

        // Income: show earned amount once on Credit.
        // Client Commission uses bill DR + receive CR; max() avoids double-counting on TB.
        if (in_array($category, self::INCOME_CATEGORIES, true)) {
            return [0.0, round(max($totalDr, $totalCr, 0), 2)];
        }

        $net = round($totalCr - $totalDr, 2);
        $flip = in_array($category, self::FLIP_NET_SIDE_CATEGORIES, true);

        if ($flip) {
            // Stored CR-heavy asset/capital offset → Debit on Final Trial Balance (and vice versa).
            $debitBalance = $net > 0 ? $net : 0.0;
            $creditBalance = $net < 0 ? abs($net) : 0.0;
        } else {
            $debitBalance = $net < 0 ? abs($net) : 0.0;
            $creditBalance = $net > 0 ? $net : 0.0;
        }

        return [$debitBalance, $creditBalance];
    }

    private function categorySortOrder(string $category): int
    {
        $index = array_search($category, self::CATEGORY_ORDER, true);

        return $index === false ? 1000 : $index;
    }

    private function accountDisplayName(FinanceAccount $account): string
    {
        $category = (string) ($account->category ?? '');

        if ($category === 'capital') {
            return "Owner's Capital";
        }

        if ($category === 'agent_advanced') {
            return 'Agent Advanced';
        }

        if ($category === 'sale') {
            return 'Sale';
        }

        if ($category === 'bills_receivable') {
            return 'Sale Receivable';
        }

        $name = trim((string) ($account->account_name ?? $account->account_label ?? ''));
        if ($name === '') {
            $name = trim((string) ($account->account_type ?? 'Account'));
        }

        $accountType = strtolower(trim((string) ($account->account_type ?? '')));
        if ($category === 'main' && $accountType === 'cash' && strcasecmp($name, 'Cash') !== 0) {
            // Keep cash accounts readable like the textbook example.
            if ($name === '' || preg_match('/^cash$/i', $name)) {
                return 'Cash';
            }
        }

        $code = trim((string) ($account->code ?? ''));
        if ($code !== '' && !in_array($category, ['main', 'capital', 'agent_advanced', 'sale', 'bills_receivable', 'income_receivable', 'expense_payable'], true)) {
            return "{$name} ({$code})";
        }

        return $name;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
