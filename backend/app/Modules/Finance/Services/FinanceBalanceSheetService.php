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
        'agent_advanced',
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
        'vendor',
        'staff',
        'principal',
        'client',
        'applicant',
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

            if (in_array($category, self::ASSET_CATEGORIES, true)) {
                $amount = round((float) ($row['debit_balance'] ?? 0), 2);
                if ($amount < 0.005) {
                    continue;
                }

                $assets[] = $this->lineItem(
                    (string) ($row['account_name'] ?? 'Account'),
                    $amount,
                    $category,
                    (int) ($row['account_id'] ?? 0)
                );

                continue;
            }

            if (in_array($category, self::LIABILITY_CATEGORIES, true)) {
                $amount = round((float) ($row['credit_balance'] ?? 0), 2);
                if ($amount < 0.005) {
                    continue;
                }

                $liabilities[] = $this->lineItem(
                    (string) ($row['account_name'] ?? 'Account'),
                    $amount,
                    $category,
                    (int) ($row['account_id'] ?? 0)
                );

                continue;
            }

            if (in_array($category, self::EQUITY_CATEGORIES, true)) {
                $credit = round((float) ($row['credit_balance'] ?? 0), 2);
                $debit = round((float) ($row['debit_balance'] ?? 0), 2);

                if ($credit >= 0.005) {
                    $equity[] = $this->lineItem(
                        (string) ($row['account_name'] ?? 'Account'),
                        $credit,
                        $category,
                        (int) ($row['account_id'] ?? 0)
                    );
                } elseif ($debit >= 0.005) {
                    // Contra-equity (e.g. Owner Drawings) reduces total equity.
                    $equity[] = $this->lineItem(
                        (string) ($row['account_name'] ?? 'Account'),
                        round(-$debit, 2),
                        $category,
                        (int) ($row['account_id'] ?? 0)
                    );
                }
            }
        }

        $statement = $this->incomeStatementService->getStatement([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        $currentYearProfit = round((float) ($statement['summary']['net_profit'] ?? 0), 2);

        if (abs($currentYearProfit) >= 0.005) {
            $equity[] = $this->lineItem(
                $currentYearProfit >= 0 ? 'Current Year Profit' : 'Current Year Loss',
                $currentYearProfit,
                'current_year_profit',
                0
            );
        }

        $assets = $this->sortLines($assets);
        $liabilities = $this->sortLines($liabilities);
        $equity = $this->sortLines($equity, true);

        $totalAssets = round(array_sum(array_column($assets, 'amount')), 2);
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
            'liabilities' => $liabilities,
            'equity' => $equity,
            'summary' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'current_year_profit' => $currentYearProfit,
                'difference' => $difference,
                'is_balanced' => $isBalanced,
            ],
        ];
    }

    /**
     * @return array{label: string, amount: float, category: string, account_id: int}
     */
    private function lineItem(string $label, float $amount, string $category, int $accountId): array
    {
        return [
            'label' => $label,
            'amount' => $amount,
            'category' => $category,
            'account_id' => $accountId,
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
                $aProfit = ($a['category'] ?? '') === 'current_year_profit' ? 1 : 0;
                $bProfit = ($b['category'] ?? '') === 'current_year_profit' ? 1 : 0;
                if ($aProfit !== $bProfit) {
                    return $aProfit <=> $bProfit;
                }
            }

            return strcasecmp($a['label'], $b['label']);
        });

        return $lines;
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
