<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\ChartOfAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GrossProfitService
{
    private const TYPE_ORDER = [
        'Revenue',
        'Contra Revenue',
        'Direct Cost A',
        'Direct Cost B',
    ];

    private const REVENUE_TYPES = [
        'Revenue',
    ];

    public function getReport(array $filters = []): array
    {
        $fromDate = $this->normalizeDate($filters['from_date'] ?? null);
        $toDate = $this->normalizeDate($filters['to_date'] ?? null) ?? now()->toDateString();

        $lineSums = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->where('journals.status', 'posted')
            ->when($fromDate, fn ($query) => $query->whereDate('journals.voucher_date', '>=', $fromDate))
            ->whereDate('journals.voucher_date', '<=', $toDate)
            ->groupBy('journal_lines.account_id')
            ->selectRaw('journal_lines.account_id, SUM(journal_lines.debit) as total_dr, SUM(journal_lines.credit) as total_cr')
            ->get()
            ->keyBy('account_id');

        $accounts = ChartOfAccount::query()
            ->where('status', 'active')
            ->where('financial_statement', 'Gross Profit')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $groups = [];
        $totalRevenue = 0.0;
        $totalContraRevenue = 0.0;
        $totalDirectCostA = 0.0;
        $totalDirectCostB = 0.0;

        foreach ($accounts as $account) {
            $sums = $lineSums->get($account->id);
            $totalDr = round((float) ($sums->total_dr ?? 0), 2);
            $totalCr = round((float) ($sums->total_cr ?? 0), 2);
            $amount = $this->resolveNetAmount((string) $account->type, $totalDr, $totalCr);

            if (abs($amount) < 0.005) {
                continue;
            }

            $type = (string) $account->type;
            if (! isset($groups[$type])) {
                $groups[$type] = [
                    'type' => $type,
                    'type_label' => $type,
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
                'Revenue' => $totalRevenue = round($totalRevenue + $amount, 2),
                'Contra Revenue' => $totalContraRevenue = round($totalContraRevenue + $amount, 2),
                'Direct Cost A' => $totalDirectCostA = round($totalDirectCostA + $amount, 2),
                'Direct Cost B' => $totalDirectCostB = round($totalDirectCostB + $amount, 2),
                default => null,
            };
        }

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

        $grossProfit = round(
            $totalRevenue - $totalContraRevenue - $totalDirectCostA - $totalDirectCostB,
            2
        );

        return [
            'title' => 'Gross Profit',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'groups' => $groupList,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_contra_revenue' => $totalContraRevenue,
                'total_direct_cost_a' => $totalDirectCostA,
                'total_direct_cost_b' => $totalDirectCostB,
                'total_direct_cost' => round($totalDirectCostA + $totalDirectCostB, 2),
                'gross_profit' => $grossProfit,
            ],
        ];
    }

    private function resolveNetAmount(string $accountType, float $totalDr, float $totalCr): float
    {
        if (in_array($accountType, self::REVENUE_TYPES, true)) {
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
