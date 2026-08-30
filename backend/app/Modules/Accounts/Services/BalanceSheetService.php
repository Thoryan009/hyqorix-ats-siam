<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\ChartOfAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    private const TYPE_ORDER = [
        'Asset',
        'Contra Asset',
        'Liability',
        'Equity',
        'Contra Equity',
    ];

    private const SECTION_MAP = [
        'Asset' => 'assets',
        'Contra Asset' => 'assets',
        'Liability' => 'liabilities',
        'Equity' => 'equity',
        'Contra Equity' => 'equity',
    ];

    public function __construct(
        private readonly IncomeStatementService $incomeStatementService,
    ) {}

    public function getReport(array $filters = []): array
    {
        $fromDate = $this->normalizeDate($filters['from_date'] ?? null);
        $toDate = $this->normalizeDate($filters['to_date'] ?? null) ?? now()->toDateString();

        if ($fromDate === null) {
            $fromDate = Carbon::parse($toDate)->startOfYear()->toDateString();
        }

        $lineSums = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->where('journals.status', 'posted')
            ->whereDate('journals.voucher_date', '<=', $toDate)
            ->groupBy('journal_lines.account_id')
            ->selectRaw('journal_lines.account_id, SUM(journal_lines.debit) as total_dr, SUM(journal_lines.credit) as total_cr')
            ->get()
            ->keyBy('account_id');

        $accounts = ChartOfAccount::query()
            ->where('status', 'active')
            ->where('financial_statement', 'Balance Sheet')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $groups = [];
        $assetTotal = 0.0;
        $contraAssetTotal = 0.0;
        $liabilityTotal = 0.0;
        $equityTotal = 0.0;
        $contraEquityTotal = 0.0;

        foreach ($accounts as $account) {
            $sums = $lineSums->get($account->id);
            $totalDr = round((float) ($sums->total_dr ?? 0), 2);
            $totalCr = round((float) ($sums->total_cr ?? 0), 2);
            $amount = $this->resolveNetAmount((string) $account->normal_balance, $totalDr, $totalCr);

            if (abs($amount) < 0.005) {
                continue;
            }

            $type = (string) $account->type;
            if (! isset($groups[$type])) {
                $groups[$type] = [
                    'type' => $type,
                    'type_label' => $type,
                    'section' => self::SECTION_MAP[$type] ?? 'other',
                    'rows' => [],
                    'section_total' => 0.0,
                ];
            }

            $groups[$type]['rows'][] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $type,
                'total_dr' => $totalDr,
                'total_cr' => $totalCr,
                'amount' => $amount,
            ];

            $groups[$type]['section_total'] = round($groups[$type]['section_total'] + $amount, 2);

            match ($type) {
                'Asset' => $assetTotal = round($assetTotal + $amount, 2),
                'Contra Asset' => $contraAssetTotal = round($contraAssetTotal + $amount, 2),
                'Liability' => $liabilityTotal = round($liabilityTotal + $amount, 2),
                'Equity' => $equityTotal = round($equityTotal + $amount, 2),
                'Contra Equity' => $contraEquityTotal = round($contraEquityTotal + $amount, 2),
                default => null,
            };
        }

        $incomeStatement = $this->incomeStatementService->getReport([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
        $currentYearProfit = round((float) ($incomeStatement['summary']['net_profit'] ?? 0), 2);

        $groupList = [];
        foreach (self::TYPE_ORDER as $type) {
            if (! isset($groups[$type])) {
                continue;
            }

            $groupList[] = $groups[$type];
        }

        foreach ($groups as $type => $group) {
            if (in_array($type, self::TYPE_ORDER, true)) {
                continue;
            }

            $groupList[] = $group;
        }

        $totalAssets = round($assetTotal - $contraAssetTotal, 2);
        $totalLiabilities = $liabilityTotal;
        $totalEquity = round($equityTotal - $contraEquityTotal + $currentYearProfit, 2);
        $totalLiabilitiesAndEquity = round($totalLiabilities + $totalEquity, 2);
        $difference = round($totalAssets - $totalLiabilitiesAndEquity, 2);

        return [
            'title' => 'Balance Sheet',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'current_year_profit' => $currentYearProfit,
            'groups' => $groupList,
            'summary' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'current_year_profit' => $currentYearProfit,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'difference' => $difference,
                'is_balanced' => abs($difference) < 0.005,
            ],
        ];
    }

    private function resolveNetAmount(string $normalBalance, float $totalDr, float $totalCr): float
    {
        if ($normalBalance === 'credit') {
            return round($totalCr - $totalDr, 2);
        }

        return round($totalDr - $totalCr, 2);
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
