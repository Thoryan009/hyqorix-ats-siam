<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\ChartOfAccount;
use App\Modules\Application\Helpers\ApplicationPresenter;
use App\Modules\Application\Models\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        $passportMap = $this->buildPassportMap($filters);
        if ($passportMap->isEmpty()) {
            return $this->emptyReport($fromDate, $toDate);
        }

        $accounts = ChartOfAccount::query()
            ->where('status', 'active')
            ->where('financial_statement', 'Gross Profit')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        if ($accounts->isEmpty()) {
            return $this->emptyReport($fromDate, $toDate);
        }

        $lines = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->where('journals.status', 'posted')
            ->when($fromDate, fn ($query) => $query->whereDate('journals.voucher_date', '>=', $fromDate))
            ->whereDate('journals.voucher_date', '<=', $toDate)
            ->whereIn('journal_lines.account_id', $accounts->keys())
            ->whereNotNull('journal_lines.sub_ledger')
            ->where('journal_lines.sub_ledger', '!=', '')
            ->select([
                'journal_lines.account_id',
                'journal_lines.sub_ledger',
                'journal_lines.debit',
                'journal_lines.credit',
            ])
            ->get();

        $accountTotals = [];
        $candidateTotals = [];

        foreach ($lines as $line) {
            $matchingPassports = $this->matchingPassports((string) $line->sub_ledger, $passportMap);
            if ($matchingPassports === []) {
                continue;
            }

            $account = $accounts->get($line->account_id);
            if (! $account) {
                continue;
            }

            $shareCount = count($matchingPassports);
            $allocatedDebit = $this->allocateAmount((float) $line->debit, $shareCount);
            $allocatedCredit = $this->allocateAmount((float) $line->credit, $shareCount);

            foreach ($matchingPassports as $index => $passport) {
                $debit = $allocatedDebit[$index];
                $credit = $allocatedCredit[$index];
                $amount = $this->resolveNetAmount((string) $account->type, $debit, $credit);

                if (! isset($accountTotals[$account->id])) {
                    $accountTotals[$account->id] = [
                        'total_dr' => 0.0,
                        'total_cr' => 0.0,
                        'amount' => 0.0,
                    ];
                }

                $accountTotals[$account->id]['total_dr'] = round($accountTotals[$account->id]['total_dr'] + $debit, 2);
                $accountTotals[$account->id]['total_cr'] = round($accountTotals[$account->id]['total_cr'] + $credit, 2);
                $accountTotals[$account->id]['amount'] = round($accountTotals[$account->id]['amount'] + $amount, 2);

                if (! isset($candidateTotals[$passport])) {
                    $candidateTotals[$passport] = $this->emptyCandidateTotals($passportMap->get($passport));
                }

                $this->accumulateCandidateAmount($candidateTotals[$passport], (string) $account->type, $amount);
            }
        }

        [$groups, $summary] = $this->buildGroupsAndSummary($accounts, $accountTotals);
        $candidates = $this->buildCandidateRows($candidateTotals);

        return [
            'title' => 'Gross Profit',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'groups' => $groups,
            'summary' => $summary,
            'candidates' => $candidates,
        ];
    }

    private function buildPassportMap(array $filters): Collection
    {
        $query = Application::query()
            ->with([
                'agent.user',
                'jobList.workOrder',
                'jobList.workOrder.client.user',
                'jobList.principal.user',
            ])
            ->whereNotNull('passport_no')
            ->where('passport_no', '!=', '');

        if (! empty($filters['job_list_id'])) {
            $query->where('job_list_id', (int) $filters['job_list_id']);
        }

        if (! empty($filters['work_order_id'])) {
            $workOrderId = (int) $filters['work_order_id'];
            $query->whereHas('jobList', fn ($jobQuery) => $jobQuery->where('work_order_id', $workOrderId));
        }

        if (! empty($filters['client_id'])) {
            $clientId = (int) $filters['client_id'];
            $query->whereHas('jobList.workOrder', fn ($workOrderQuery) => $workOrderQuery->where('client_id', $clientId));
        }

        if (! empty($filters['agent_id'])) {
            $query->where('agent_id', (int) $filters['agent_id']);
        }

        if (! empty($filters['principal_id'])) {
            $principalId = (int) $filters['principal_id'];
            $query->whereHas('jobList', fn ($jobQuery) => $jobQuery->where('principal_id', $principalId));
        }

        return $query
            ->get()
            ->mapWithKeys(function (Application $application) {
                $passport = strtoupper(trim((string) $application->passport_no));
                if ($passport === '') {
                    return [];
                }

                $job = $application->jobList;
                $workOrder = $job?->workOrder;

                return [
                    $passport => [
                        'passport_no' => $passport,
                        'candidate_name' => ApplicationPresenter::fullName(
                            $application->given_name ?? '',
                            $application->sur_name ?? '',
                        ),
                        'job_list_id' => $application->job_list_id,
                        'job_name' => $job?->name,
                        'work_order_id' => $job?->work_order_id,
                        'demand_letter' => $workOrder?->work_order_id,
                        'client_name' => $workOrder?->client?->user?->name,
                        'agent_name' => $application->agent?->user?->name,
                        'principal_name' => $job?->principal?->user?->name,
                    ],
                ];
            });
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $passportMap
     * @return list<string>
     */
    private function matchingPassports(string $subLedger, Collection $passportMap): array
    {
        $passports = $this->parseSubLedgerPassports($subLedger);

        return array_values(array_filter(
            $passports,
            fn (string $passport) => $passportMap->has($passport),
        ));
    }

    /**
     * @return list<string>
     */
    private function parseSubLedgerPassports(string $value): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (string $passport) => strtoupper(trim($passport)),
            preg_split('/\s*,\s*/', $value) ?: [],
        ))));
    }

    /**
     * @return list<float>
     */
    private function allocateAmount(float $amount, int $shareCount): array
    {
        if ($shareCount <= 0) {
            return [];
        }

        $roundedTotal = round($amount, 2);
        $baseShare = round($roundedTotal / $shareCount, 2);
        $shares = array_fill(0, $shareCount, $baseShare);
        $allocated = round($baseShare * $shareCount, 2);
        $remainder = round($roundedTotal - $allocated, 2);

        if ($remainder !== 0.0) {
            $shares[$shareCount - 1] = round($shares[$shareCount - 1] + $remainder, 2);
        }

        return $shares;
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @return array<string, mixed>
     */
    private function emptyCandidateTotals(?array $candidate): array
    {
        return array_merge($candidate ?? [
            'passport_no' => '',
            'candidate_name' => null,
            'job_list_id' => null,
            'job_name' => null,
            'work_order_id' => null,
            'demand_letter' => null,
            'client_name' => null,
            'agent_name' => null,
            'principal_name' => null,
        ], [
            'revenue' => 0.0,
            'contra_revenue' => 0.0,
            'direct_cost_a' => 0.0,
            'direct_cost_b' => 0.0,
            'direct_cost' => 0.0,
            'gross_profit' => 0.0,
        ]);
    }

    private function accumulateCandidateAmount(array &$candidate, string $accountType, float $amount): void
    {
        match ($accountType) {
            'Revenue' => $candidate['revenue'] = round($candidate['revenue'] + $amount, 2),
            'Contra Revenue' => $candidate['contra_revenue'] = round($candidate['contra_revenue'] + $amount, 2),
            'Direct Cost A' => $candidate['direct_cost_a'] = round($candidate['direct_cost_a'] + $amount, 2),
            'Direct Cost B' => $candidate['direct_cost_b'] = round($candidate['direct_cost_b'] + $amount, 2),
            default => null,
        };

        $candidate['direct_cost'] = round($candidate['direct_cost_a'] + $candidate['direct_cost_b'], 2);
        $candidate['gross_profit'] = round(
            $candidate['revenue'] - $candidate['contra_revenue'] - $candidate['direct_cost'],
            2,
        );
    }

    /**
     * @param  Collection<int, ChartOfAccount>  $accounts
     * @param  array<int, array{total_dr: float, total_cr: float, amount: float}>  $accountTotals
     * @return array{0: list<array<string, mixed>>, 1: array<string, float>}
     */
    private function buildGroupsAndSummary(Collection $accounts, array $accountTotals): array
    {
        $groups = [];
        $totalRevenue = 0.0;
        $totalContraRevenue = 0.0;
        $totalDirectCostA = 0.0;
        $totalDirectCostB = 0.0;

        foreach ($accounts as $account) {
            $totals = $accountTotals[$account->id] ?? null;
            if (! $totals || abs($totals['amount']) < 0.005) {
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
                'total_dr' => $totals['total_dr'],
                'total_cr' => $totals['total_cr'],
                'amount' => $totals['amount'],
            ];

            $groups[$type]['section_total'] = round($groups[$type]['section_total'] + $totals['amount'], 2);

            match ($type) {
                'Revenue' => $totalRevenue = round($totalRevenue + $totals['amount'], 2),
                'Contra Revenue' => $totalContraRevenue = round($totalContraRevenue + $totals['amount'], 2),
                'Direct Cost A' => $totalDirectCostA = round($totalDirectCostA + $totals['amount'], 2),
                'Direct Cost B' => $totalDirectCostB = round($totalDirectCostB + $totals['amount'], 2),
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
            2,
        );

        return [
            $groupList,
            [
                'total_revenue' => $totalRevenue,
                'total_contra_revenue' => $totalContraRevenue,
                'total_direct_cost_a' => $totalDirectCostA,
                'total_direct_cost_b' => $totalDirectCostB,
                'total_direct_cost' => round($totalDirectCostA + $totalDirectCostB, 2),
                'gross_profit' => $grossProfit,
            ],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $candidateTotals
     * @return list<array<string, mixed>>
     */
    private function buildCandidateRows(array $candidateTotals): array
    {
        return collect($candidateTotals)
            ->filter(fn (array $candidate) => abs((float) ($candidate['gross_profit'] ?? 0)) >= 0.005
                || abs((float) ($candidate['revenue'] ?? 0)) >= 0.005
                || abs((float) ($candidate['direct_cost'] ?? 0)) >= 0.005)
            ->sortBy('passport_no')
            ->values()
            ->all();
    }

    private function emptyReport(?string $fromDate, string $toDate): array
    {
        return [
            'title' => 'Gross Profit',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'groups' => [],
            'summary' => [
                'total_revenue' => 0.0,
                'total_contra_revenue' => 0.0,
                'total_direct_cost_a' => 0.0,
                'total_direct_cost_b' => 0.0,
                'total_direct_cost' => 0.0,
                'gross_profit' => 0.0,
            ],
            'candidates' => [],
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
