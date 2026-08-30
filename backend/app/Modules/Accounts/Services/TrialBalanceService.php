<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\ChartOfAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
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
            ->orderBy('sort_order')
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $rows = [];
        $debitTotal = 0.0;
        $creditTotal = 0.0;

        foreach ($accounts as $account) {
            $sums = $lineSums->get($account->id);
            $totalDr = round((float) ($sums->total_dr ?? 0), 2);
            $totalCr = round((float) ($sums->total_cr ?? 0), 2);

            [$debitBalance, $creditBalance] = $this->resolvePresentationBalances(
                (string) $account->normal_balance,
                $totalDr,
                $totalCr,
            );

            if ($debitBalance < 0.005 && $creditBalance < 0.005) {
                continue;
            }

            $rows[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'financial_statement' => $account->financial_statement,
                'normal_balance' => $account->normal_balance,
                'total_dr' => $totalDr,
                'total_cr' => $totalCr,
                'debit_balance' => $debitBalance,
                'credit_balance' => $creditBalance,
            ];

            $debitTotal = round($debitTotal + $debitBalance, 2);
            $creditTotal = round($creditTotal + $creditBalance, 2);
        }

        $groups = [];
        foreach ($rows as $row) {
            $type = (string) ($row['account_type'] ?: 'Other');
            if (! isset($groups[$type])) {
                $groups[$type] = [
                    'type' => $type,
                    'type_label' => $type,
                    'rows' => [],
                    'debit_total' => 0.0,
                    'credit_total' => 0.0,
                ];
            }

            $groups[$type]['rows'][] = $row;
            $groups[$type]['debit_total'] = round($groups[$type]['debit_total'] + $row['debit_balance'], 2);
            $groups[$type]['credit_total'] = round($groups[$type]['credit_total'] + $row['credit_balance'], 2);
        }

        $groupList = array_values($groups);
        $difference = round($debitTotal - $creditTotal, 2);
        $isBalanced = abs($difference) < 0.005;

        return [
            'title' => 'Trial Balance',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'groups' => $groupList,
            'rows' => $rows,
            'debit_total' => $debitTotal,
            'credit_total' => $creditTotal,
            'difference' => $difference,
            'is_balanced' => $isBalanced,
            'summary' => [
                'account_count' => count($rows),
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
        string $normalBalance,
        float $totalDr,
        float $totalCr,
    ): array {
        if ($normalBalance === 'credit') {
            $net = round($totalCr - $totalDr, 2);
            $creditBalance = $net > 0 ? $net : 0.0;
            $debitBalance = $net < 0 ? abs($net) : 0.0;

            return [$debitBalance, $creditBalance];
        }

        $net = round($totalDr - $totalCr, 2);
        $debitBalance = $net > 0 ? $net : 0.0;
        $creditBalance = $net < 0 ? abs($net) : 0.0;

        return [$debitBalance, $creditBalance];
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
