<?php

namespace App\Modules\Finance\Services;

use Illuminate\Support\Carbon;

class FinanceBalanceSheetService
{
    /** Balance sheet — Assets (debit-normal). */
    private const ASSET_CATEGORIES = [
        'main',
        'asset',
        'bills_receivable',
        'income_receivable',
    ];

    /** Balance sheet — Liabilities (credit-normal). */
    private const LIABILITY_CATEGORIES = [
        'liabilities',
        'expense_payable',
    ];

    /** Balance sheet — Owner's equity ledger accounts (excludes current-year P&L). */
    private const EQUITY_CATEGORIES = [
        'capital',
        'owners_equity',
    ];

    /** P&L income heads on Trial Balance (credit-normal) — close to equity, not shown as lines. */
    private const INCOME_CATEGORIES = [
        'sale',
        'recruitment_income',
        'client_income',
        'other_income',
    ];

    /** P&L expense heads on Trial Balance (debit-normal) — close to equity, not shown as lines. */
    private const EXPENSE_CATEGORIES = [
        'direct_expense',
        'client_recruitment',
        'operating_expense',
    ];

    /** P&L / party categories excluded from line items (profit closes to equity). */
    private const EXCLUDED_CATEGORIES = [
        'direct_expense',
        'client_recruitment',
        'operating_expense',
        'sale',
        'recruitment_income',
        'client_income',
        'other_income',
        'agent',
        'agent_advanced',
        'vendor',
        'staff',
        'principal',
        'client',
        'applicant',
        'banks',
        'owners',
    ];

    public function __construct(
        private readonly FinanceTrialBalanceService $trialBalanceService,
        private readonly FinanceIncomeStatementService $incomeStatementService,
    ) {}

    public function getReport(array $filters = []): array
    {
        $fromDate = $this->normalizeDate($filters['from_date'] ?? null);
        $toDate = $this->normalizeDate($filters['to_date'] ?? null) ?? now()->toDateString();

        if ($fromDate === null) {
            $fromDate = Carbon::parse($toDate)->startOfYear()->toDateString();
        }

        $trialBalance = $this->trialBalanceService->getReport([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        $assets = [];
        $liabilities = [];
        $equity = [];

        foreach ($trialBalance['rows'] ?? [] as $row) {
            $category = (string) ($row['category'] ?? '');

            if (in_array($category, self::EXCLUDED_CATEGORIES, true)) {
                continue;
            }

            $debit = round((float) ($row['debit_balance'] ?? 0), 2);
            $credit = round((float) ($row['credit_balance'] ?? 0), 2);
            $label = (string) ($row['account_name'] ?? 'Account');
            $accountId = (int) ($row['account_id'] ?? 0);

            if (in_array($category, self::ASSET_CATEGORIES, true)) {
                $net = round($debit - $credit, 2);
                $isNonCurrent = $category === 'asset'
                    && (bool) ($row['is_non_current_asset'] ?? false);
                if ($net > 0.005) {
                    $assets[] = $this->lineItem($label, $net, $category, $accountId, $isNonCurrent);
                } elseif ($net < -0.005) {
                    $liabilities[] = $this->lineItem($label, abs($net), $category, $accountId);
                }

                continue;
            }

            if (in_array($category, self::LIABILITY_CATEGORIES, true)) {
                $net = round($credit - $debit, 2);
                if ($net > 0.005) {
                    $liabilities[] = $this->lineItem($label, $net, $category, $accountId);
                } elseif ($net < -0.005) {
                    $assets[] = $this->lineItem($label, abs($net), $category, $accountId);
                }

                continue;
            }

            if (in_array($category, self::EQUITY_CATEGORIES, true)) {
                if ($credit >= 0.005) {
                    $equity[] = $this->lineItem($label, $credit, $category, $accountId);
                } elseif ($debit >= 0.005) {
                    // Contra-equity (e.g. Owner Drawings) reduces total equity.
                    $equity[] = $this->lineItem($label, round(-$debit, 2), $category, $accountId);
                }
            }
        }

        $statement = $this->incomeStatementService->getStatement([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        $currentYearProfit = round((float) ($statement['summary']['net_profit'] ?? 0), 2);
        $trialBalanceNetProfit = $this->computeTrialBalanceNetProfit($trialBalance['rows'] ?? []);

        if (abs($currentYearProfit) >= 0.005) {
            $equity[] = $this->lineItem(
                $currentYearProfit >= 0 ? 'Current Year Profit' : 'Current Year Loss',
                $currentYearProfit,
                'current_year_profit',
                0
            );
        }

        $currentAssets = $this->sortLines(
            array_values(array_filter(
                $assets,
                fn (array $line) => empty($line['is_non_current_asset'])
            ))
        );
        $nonCurrentAssets = $this->sortLines(
            array_values(array_filter(
                $assets,
                fn (array $line) => !empty($line['is_non_current_asset'])
            ))
        );
        $assets = $this->sortLines($assets);
        $liabilities = $this->sortLines($liabilities);
        $equity = $this->sortLines($equity, true);

        $totalCurrentAssets = round(array_sum(array_column($currentAssets, 'amount')), 2);
        $totalNonCurrentAssets = round(array_sum(array_column($nonCurrentAssets, 'amount')), 2);
        $totalAssets = round($totalCurrentAssets + $totalNonCurrentAssets, 2);
        $totalLiabilities = round(array_sum(array_column($liabilities, 'amount')), 2);
        $totalEquity = round(array_sum(array_column($equity, 'amount')), 2);
        $totalLiabilitiesAndEquity = round($totalLiabilities + $totalEquity, 2);
        $difference = round($totalAssets - $totalLiabilitiesAndEquity, 2);
        $isBalanced = abs($difference) < 0.005;

        return [
            'title' => 'Balance Sheet',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'assets' => $assets,
            'current_assets' => $currentAssets,
            'non_current_assets' => $nonCurrentAssets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'summary' => [
                'total_current_assets' => $totalCurrentAssets,
                'total_non_current_assets' => $totalNonCurrentAssets,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'current_year_profit' => $currentYearProfit,
                'trial_balance_net_profit' => $trialBalanceNetProfit,
                'trial_balance_is_balanced' => (bool) ($trialBalance['is_balanced'] ?? false),
                'difference' => $difference,
                'is_balanced' => $isBalanced,
            ],
        ];
    }

    /**
     * Net P&L per Final Trial Balance (income credits − expense debits, cumulative through to_date).
     */
    private function computeTrialBalanceNetProfit(array $rows): float
    {
        $incomeTotal = 0.0;
        $expenseTotal = 0.0;

        foreach ($rows as $row) {
            $category = (string) ($row['category'] ?? '');

            if (in_array($category, self::INCOME_CATEGORIES, true)) {
                $incomeTotal = round($incomeTotal + (float) ($row['credit_balance'] ?? 0), 2);
            }

            if (in_array($category, self::EXPENSE_CATEGORIES, true)) {
                $expenseTotal = round($expenseTotal + (float) ($row['debit_balance'] ?? 0), 2);
            }
        }

        return round($incomeTotal - $expenseTotal, 2);
    }

    /**
     * @return array{label: string, amount: float, category: string, account_id: int, is_non_current_asset: bool}
     */
    private function lineItem(
        string $label,
        float $amount,
        string $category,
        int $accountId,
        bool $isNonCurrentAsset = false,
    ): array {
        return [
            'label' => $label,
            'amount' => $amount,
            'category' => $category,
            'account_id' => $accountId,
            'is_non_current_asset' => $isNonCurrentAsset,
        ];
    }

    /**
     * @param  array<int, array{label: string, amount: float, category: string, account_id: int}>  $lines
     * @return array<int, array{label: string, amount: float, category: string, account_id: int}>
     */
    private function sortLines(array $lines, bool $profitLast = false): array
    {
        usort($lines, function (array $a, array $b) use ($profitLast) {
            if ($profitLast) {
                $aRank = $this->equityClosingSortRank((string) ($a['category'] ?? ''));
                $bRank = $this->equityClosingSortRank((string) ($b['category'] ?? ''));
                if ($aRank !== $bRank) {
                    return $aRank <=> $bRank;
                }
            }

            return strcasecmp($a['label'], $b['label']);
        });

        return $lines;
    }

    private function equityClosingSortRank(string $category): int
    {
        return match ($category) {
            'current_year_profit' => 1,
            default => 0,
        };
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
