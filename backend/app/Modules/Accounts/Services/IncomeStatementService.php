<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\ChartOfAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeStatementService
{
    private const TYPE_ORDER = [
        'Other Operating Revenue',
        'Other Income',
        'Operating Expense',
        'Finance Cost',
        'Other Expense',
    ];

    private const INCOME_TYPES = [
        'Other Operating Revenue',
        'Other Income',
    ];

    public function __construct(
        private readonly GrossProfitService $grossProfitService,
    ) {}

    public function getReport(array $filters = []): array
    {
        $fromDate = $this->normalizeDate($filters['from_date'] ?? null);
        $toDate = $this->normalizeDate($filters['to_date'] ?? null) ?? now()->toDateString();
        $filterPayload = [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        $grossProfitReport = $this->grossProfitService->getReport($filterPayload);
        $grossProfitBroughtDown = round((float) ($grossProfitReport['summary']['gross_profit'] ?? 0), 2);

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
            ->where('financial_statement', 'Income Statement')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $groups = [];
        $totalOtherOperatingRevenue = 0.0;
        $totalOtherIncome = 0.0;
        $totalOperatingExpense = 0.0;
        $totalFinanceCost = 0.0;
        $totalOtherExpense = 0.0;

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
                'Other Operating Revenue' => $totalOtherOperatingRevenue = round($totalOtherOperatingRevenue + $amount, 2),
                'Other Income' => $totalOtherIncome = round($totalOtherIncome + $amount, 2),
                'Operating Expense' => $totalOperatingExpense = round($totalOperatingExpense + $amount, 2),
                'Finance Cost' => $totalFinanceCost = round($totalFinanceCost + $amount, 2),
                'Other Expense' => $totalOtherExpense = round($totalOtherExpense + $amount, 2),
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

        $totalIncome = round($totalOtherOperatingRevenue + $totalOtherIncome, 2);
        $totalExpenses = round($totalOperatingExpense + $totalFinanceCost + $totalOtherExpense, 2);
        $netProfit = round($grossProfitBroughtDown + $totalIncome - $totalExpenses, 2);

        return [
            'title' => 'Income Statement',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'gross_profit_brought_down' => $grossProfitBroughtDown,
            'groups' => $groupList,
            'summary' => [
                'gross_profit_brought_down' => $grossProfitBroughtDown,
                'total_other_operating_revenue' => $totalOtherOperatingRevenue,
                'total_other_income' => $totalOtherIncome,
                'total_income' => $totalIncome,
                'total_operating_expense' => $totalOperatingExpense,
                'total_finance_cost' => $totalFinanceCost,
                'total_other_expense' => $totalOtherExpense,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'is_profit' => $netProfit >= 0,
            ],
        ];
    }

    private function resolveNetAmount(string $accountType, float $totalDr, float $totalCr): float
    {
        if (in_array($accountType, self::INCOME_TYPES, true)) {
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
