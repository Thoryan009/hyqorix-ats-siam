<?php

namespace App\Modules\Finance\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Finance\Models\ExpenseCategory;
use App\Modules\Finance\Models\ExpenseHead;
use App\Modules\Finance\Models\FinanceBillEntry;
use App\Modules\Finance\Models\FinanceSaleCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceGrossProfitReportService
{
    public function getReport(array $filters = []): array
    {
        $category = ExpenseCategory::query()
            ->where('code', 'direct_cost')
            ->first();

        $clientRecruitmentCategory = ExpenseCategory::query()
            ->where('code', 'client_recruitment_cost')
            ->first();

        $heads = $category
            ? ExpenseHead::query()
                ->where('expense_category_id', $category->id)
                ->orderBy('id')
                ->get(['id', 'name'])
            : collect();

        $expenseHeads = $heads->map(fn (ExpenseHead $head) => [
            'id' => $head->id,
            'name' => $head->name,
        ])->values()->all();

        $jobListIds = $this->normalizeIdList($filters['job_list_ids'] ?? null);
        $clientIds = $this->normalizeIdList($filters['client_ids'] ?? null);
        $workOrderIdsFilter = $this->normalizeIdList($filters['work_order_ids'] ?? null);

        $billQuery = FinanceBillEntry::query()
            ->whereNotNull('application_id')
            ->where('status', 'approved');

        if ($category) {
            $billQuery->where('expense_category_id', $category->id);
        }

        if (!empty($filters['from_date'])) {
            $billQuery->whereDate('payment_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $billQuery->whereDate('payment_date', '<=', $filters['to_date']);
        }

        $expenseRows = $billQuery
            ->select([
                'application_id',
                'expense_head_id',
                DB::raw('MAX(candidate_name) as candidate_name'),
                DB::raw('MAX(passport_no) as passport_no'),
                DB::raw('MAX(job_list_id) as job_list_id'),
                DB::raw('MAX(job_code) as job_code'),
                DB::raw('MAX(job_name) as job_name'),
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->groupBy('application_id', 'expense_head_id')
            ->get();

        $saleQuery = FinanceSaleCollection::query();

        if (!empty($filters['from_date'])) {
            $saleQuery->whereDate('collection_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $saleQuery->whereDate('collection_date', '<=', $filters['to_date']);
        }

        $saleRows = $saleQuery
            ->select([
                'application_id',
                DB::raw('MAX(candidate_name) as candidate_name'),
                DB::raw('MAX(passport_no) as passport_no'),
                DB::raw('MAX(job_list_id) as job_list_id'),
                DB::raw('MAX(job_code) as job_code'),
                DB::raw('MAX(job_title) as job_title'),
                DB::raw('MAX(sale_price) as sale_price'),
                DB::raw('SUM(amount) as collected_amount'),
            ])
            ->groupBy('application_id')
            ->get()
            ->keyBy('application_id');

        $applicationIds = $expenseRows
            ->pluck('application_id')
            ->merge($saleRows->keys())
            ->unique()
            ->filter()
            ->values();

        $expensesByApplication = $expenseRows->groupBy('application_id');

        $applications = Application::query()
            ->with([
                'currentProcess.process',
                'jobList.workOrder.client.user',
            ])
            ->whereIn('id', $applicationIds)
            ->get()
            ->keyBy('id');

        $workOrderIds = $applications
            ->map(fn (Application $app) => (int) ($app->jobList?->work_order_id ?? 0))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $avgCreByWorkOrder = $this->buildAvgClientRecruitmentExpenseByWorkOrder(
            $workOrderIds,
            $clientRecruitmentCategory?->id
        );

        $allRows = [];

        foreach ($applicationIds as $applicationId) {
            $expenseGroup = $expensesByApplication->get($applicationId, collect());
            $sale = $saleRows->get($applicationId);
            $application = $applications->get($applicationId);

            $expenses = [];
            foreach ($heads as $head) {
                $expenses[(string) $head->id] = 0.0;
            }

            $candidateName = '';
            $passportNo = '';
            $jobListId = null;
            $jobCode = '';
            $jobName = '';

            /** @var Collection $expenseGroup */
            foreach ($expenseGroup as $expenseRow) {
                $headId = (string) $expenseRow->expense_head_id;
                $expenses[$headId] = round((float) $expenseRow->total_amount, 2);
                $candidateName = $expenseRow->candidate_name ?: $candidateName;
                $passportNo = $expenseRow->passport_no ?: $passportNo;
                $jobListId = $expenseRow->job_list_id ?: $jobListId;
                $jobCode = $expenseRow->job_code ?: $jobCode;
                $jobName = $expenseRow->job_name ?: $jobName;
            }

            if ($sale) {
                $candidateName = $sale->candidate_name ?: $candidateName;
                $passportNo = $sale->passport_no ?: $passportNo;
                $jobListId = $sale->job_list_id ?: $jobListId;
                $jobCode = $sale->job_code ?: $jobCode;
                $jobName = $sale->job_title ?: $jobName;
            }

            if ($application) {
                $passportNo = $passportNo ?: (string) ($application->passport_no ?? '');
                $jobListId = $jobListId ?: $application->job_list_id;
                $jobCode = $jobCode ?: (string) ($application->jobList?->job_code ?? '');
                $jobName = $jobName ?: (string) ($application->jobList?->name ?? '');
            }

            $workOrder = $application?->jobList?->workOrder;
            $workOrderId = (int) ($workOrder?->id ?? $application?->jobList?->work_order_id ?? 0);
            $demandLetter = (string) ($workOrder?->work_order_id ?? '');
            $clientId = $workOrder?->client_id ? (int) $workOrder->client_id : null;
            $clientName = (string) ($workOrder?->client?->user?->name ?? '');

            $rowExpenseTotal = round(array_sum($expenses), 2);
            $salePrice = round((float) ($sale->sale_price ?? 0), 2);
            $collectedAmount = round((float) ($sale->collected_amount ?? 0), 2);
            $currentProcess = $application?->resolved_current_process
                ?: $application?->current_process_name
                ?: 'hiring_list';

            $isExcludedProcess = in_array(strtolower((string) $currentProcess), ['declined', 'rejected'], true)
                || in_array(strtolower((string) ($application?->currentProcess?->status ?? '')), ['declined', 'rejected'], true);

            $avgCre = $isExcludedProcess
                ? 0.0
                : round((float) ($avgCreByWorkOrder[$workOrderId] ?? 0), 2);
            $totalExpense = round($rowExpenseTotal + $avgCre, 2);
            $profitLoss = round($salePrice - $totalExpense, 2);

            $allRows[] = [
                'application_id' => (int) $applicationId,
                'candidate_name' => $candidateName ?: ('Candidate #'.$applicationId),
                'passport_no' => $passportNo,
                'job_list_id' => $jobListId ? (int) $jobListId : null,
                'job_code' => $jobCode,
                'job_name' => $jobName,
                'client_id' => $clientId,
                'client_name' => $clientName,
                'work_order_id' => $workOrderId ?: null,
                'demand_letter' => $demandLetter,
                'current_process' => $currentProcess,
                'expenses' => $expenses,
                'direct_expense' => $rowExpenseTotal,
                'total_expense' => $totalExpense,
                'avg_cre' => $avgCre,
                'sale_price' => $salePrice,
                'collected_amount' => $collectedAmount,
                'profit_loss' => $profitLoss,
                'is_rejected_declined' => $isExcludedProcess,
            ];
        }

        $filterOptions = $this->buildFilterOptions($allRows);

        $filteredRows = array_values(array_filter(
            $allRows,
            fn (array $row) => $this->matchesDimensionFilters(
                $row,
                $jobListIds,
                $clientIds,
                $workOrderIdsFilter
            )
        ));

        if (!empty($filters['search'])) {
            $search = mb_strtolower(trim((string) $filters['search']));
            $filteredRows = array_values(array_filter($filteredRows, function (array $row) use ($search) {
                return str_contains(mb_strtolower($row['candidate_name']), $search)
                    || str_contains(mb_strtolower((string) $row['passport_no']), $search)
                    || str_contains(mb_strtolower((string) $row['job_code']), $search)
                    || str_contains(mb_strtolower((string) $row['job_name']), $search)
                    || str_contains(mb_strtolower((string) $row['client_name']), $search)
                    || str_contains(mb_strtolower((string) $row['demand_letter']), $search);
            }));
        }

        $rows = [];
        $rejectedDeclinedCount = 0;
        $rejectedDeclinedExpense = 0.0;

        foreach ($filteredRows as $row) {
            if (!empty($row['is_rejected_declined'])) {
                $rejectedDeclinedCount++;
                $rejectedDeclinedExpense += (float) $row['direct_expense'];
                continue;
            }

            unset($row['is_rejected_declined']);
            $rows[] = $row;
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['candidate_name'], $b['candidate_name']));

        $totalSale = 0.0;
        $totalExpense = 0.0;
        $totalAvgCre = 0.0;
        $totalProfit = 0.0;
        $totalCollected = 0.0;

        foreach ($rows as $row) {
            $totalSale += (float) $row['sale_price'];
            $totalExpense += (float) $row['total_expense'];
            $totalAvgCre += (float) $row['avg_cre'];
            $totalProfit += (float) $row['profit_loss'];
            $totalCollected += (float) $row['collected_amount'];
        }

        $rejectedDeclinedExpense = round($rejectedDeclinedExpense, 2);
        $adjustedGrossProfitLoss = round($totalProfit - $rejectedDeclinedExpense, 2);
        $activeCount = count($rows);
        $adjustedAvgProfitLoss = $activeCount > 0
            ? round($adjustedGrossProfitLoss / $activeCount, 2)
            : 0.0;

        foreach ($rows as $index => $row) {
            $rows[$index]['adjusted_avg_profit_loss'] = $adjustedAvgProfitLoss;
        }

        return [
            'expense_heads' => $expenseHeads,
            'filter_options' => $filterOptions,
            'rows' => $rows,
            'summary' => [
                'candidate_count' => $activeCount,
                'total_sale' => round($totalSale, 2),
                'total_collected' => round($totalCollected, 2),
                'total_expense' => round($totalExpense, 2),
                'total_avg_cre' => round($totalAvgCre, 2),
                'total_profit_loss' => round($totalProfit, 2),
                'adjusted_avg_profit_loss' => $adjustedAvgProfitLoss,
                'rejected_declined_count' => $rejectedDeclinedCount,
                'rejected_declined_expense' => $rejectedDeclinedExpense,
                'adjusted_gross_profit_loss' => $adjustedGrossProfitLoss,
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{jobs: list<array{id: int, label: string}>, clients: list<array{id: int, label: string}>, demand_letters: list<array{id: int, label: string}>}
     */
    private function buildFilterOptions(array $rows): array
    {
        $jobs = [];
        $clients = [];
        $demandLetters = [];

        foreach ($rows as $row) {
            $jobListId = (int) ($row['job_list_id'] ?? 0);
            if ($jobListId > 0 && !isset($jobs[$jobListId])) {
                $code = trim((string) ($row['job_code'] ?? ''));
                $name = trim((string) ($row['job_name'] ?? ''));
                $label = $code && $name
                    ? "{$code} — {$name}"
                    : ($code ?: ($name ?: "Job #{$jobListId}"));
                $jobs[$jobListId] = [
                    'id' => $jobListId,
                    'label' => $label,
                ];
            }

            $clientId = (int) ($row['client_id'] ?? 0);
            if ($clientId > 0 && !isset($clients[$clientId])) {
                $clients[$clientId] = [
                    'id' => $clientId,
                    'label' => trim((string) ($row['client_name'] ?? '')) ?: "Client #{$clientId}",
                ];
            }

            $workOrderId = (int) ($row['work_order_id'] ?? 0);
            if ($workOrderId > 0 && !isset($demandLetters[$workOrderId])) {
                $demandLetters[$workOrderId] = [
                    'id' => $workOrderId,
                    'label' => trim((string) ($row['demand_letter'] ?? '')) ?: "DL #{$workOrderId}",
                ];
            }
        }

        $sortByLabel = fn (array $a, array $b) => strcasecmp($a['label'], $b['label']);

        $jobOptions = array_values($jobs);
        $clientOptions = array_values($clients);
        $demandLetterOptions = array_values($demandLetters);

        usort($jobOptions, $sortByLabel);
        usort($clientOptions, $sortByLabel);
        usort($demandLetterOptions, $sortByLabel);

        return [
            'jobs' => $jobOptions,
            'clients' => $clientOptions,
            'demand_letters' => $demandLetterOptions,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<int>  $jobListIds
     * @param  list<int>  $clientIds
     * @param  list<int>  $workOrderIds
     */
    private function matchesDimensionFilters(
        array $row,
        array $jobListIds,
        array $clientIds,
        array $workOrderIds
    ): bool {
        if ($jobListIds !== [] && !in_array((int) ($row['job_list_id'] ?? 0), $jobListIds, true)) {
            return false;
        }

        if ($clientIds !== [] && !in_array((int) ($row['client_id'] ?? 0), $clientIds, true)) {
            return false;
        }

        if ($workOrderIds !== [] && !in_array((int) ($row['work_order_id'] ?? 0), $workOrderIds, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return list<int>
     */
    private function normalizeIdList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        return array_values(array_unique(array_filter(
            array_map(static fn ($id) => (int) $id, $value),
            static fn (int $id) => $id > 0
        )));
    }

    /**
     * Avg C.R.E. per demand letter =
     * (sum of approved Client Recruitment Expense bills) /
     * (unique candidates in Receipt List / payment-collection for that DL,
     *  excluding declined/rejected current process).
     *
     * @param  list<int>  $workOrderIds
     * @return array<int, float>
     */
    private function buildAvgClientRecruitmentExpenseByWorkOrder(array $workOrderIds, ?int $creCategoryId): array
    {
        if ($workOrderIds === [] || !$creCategoryId) {
            return [];
        }

        $creTotals = FinanceBillEntry::query()
            ->where('expense_category_id', $creCategoryId)
            ->where('status', 'approved')
            ->whereIn('work_order_id', $workOrderIds)
            ->select([
                'work_order_id',
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->groupBy('work_order_id')
            ->pluck('total_amount', 'work_order_id');

        $jobListIds = DB::table('job_lists')
            ->whereIn('work_order_id', $workOrderIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if ($jobListIds === []) {
            return array_fill_keys($workOrderIds, 0.0);
        }

        // Receipt List candidates (payment-collection) for jobs under these DLs.
        $receiptApplicationIds = FinanceSaleCollection::query()
            ->where(function ($query) use ($jobListIds) {
                $query
                    ->whereIn('job_list_id', $jobListIds)
                    ->orWhereIn('application_id', function ($sub) use ($jobListIds) {
                        $sub->select('id')
                            ->from('applications')
                            ->whereIn('job_list_id', $jobListIds);
                    });
            })
            ->distinct()
            ->pluck('application_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if ($receiptApplicationIds === []) {
            return array_fill_keys($workOrderIds, 0.0);
        }

        $applications = Application::query()
            ->with(['currentProcess.process', 'jobList'])
            ->whereIn('id', $receiptApplicationIds)
            ->get(['id', 'job_list_id']);

        $counts = [];
        foreach ($applications as $application) {
            $processStatus = strtolower((string) ($application->currentProcess?->status ?? ''));
            $processName = strtolower((string) (
                $application->resolved_current_process
                ?? $application->currentProcess?->process?->name
                ?? ''
            ));

            if (
                in_array($processStatus, ['declined', 'rejected'], true)
                || in_array($processName, ['declined', 'rejected'], true)
            ) {
                continue;
            }

            $workOrderId = (int) ($application->jobList?->work_order_id ?? 0);
            if ($workOrderId <= 0 || !in_array($workOrderId, $workOrderIds, true)) {
                continue;
            }

            $counts[$workOrderId] = ($counts[$workOrderId] ?? 0) + 1;
        }

        $averages = [];
        foreach ($workOrderIds as $workOrderId) {
            $count = (int) ($counts[$workOrderId] ?? 0);
            $total = (float) ($creTotals[$workOrderId] ?? 0);
            $averages[$workOrderId] = $count > 0 ? round($total / $count, 2) : 0.0;
        }

        return $averages;
    }
}
