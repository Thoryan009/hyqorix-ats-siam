<?php

namespace App\Modules\Application\Repositories;

use App\Modules\Application\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Application\Services\ProcessService;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class ApplicationRepository extends BaseRepository
{
    protected ProcessService $processService;

    public function __construct(Application $model, ProcessService $processService)
    {
        $this->model = $model;
        $this->processService = $processService;
    }

    /**
     * Public method to get paginated applications
     */
    protected function baseQuery(): Builder
    {
        return $this->model->newQuery()
            ->with(['currentProcess', 'processes', 'jobList', 'experiences']);
    }




    /* ================= INTERNAL HELPERS ================= */

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyJobListFilter($query, $filters['job_list_id'] ?? null);
        $this->applyWorkOrderFilter($query, $filters['work_order_id'] ?? null);
        $this->applyClientFilter($query, $filters['client_id'] ?? null);
        $this->applyAgentFilter($query, $filters['agent_id'] ?? null);
        $this->applyPaymentResponsibilityFilter($query, $filters['payment_responsibility'] ?? null);
        $this->applyProcessFilter($query, $filters['process_id'] ?? null);
        $this->applyApplicationStatusFilter($query, $filters['application_status'] ?? null);
        $this->applyAgentApplicationsFilter($query, $filters['application_ids'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applyClientFilter(Builder $query, ?int $clientId): void
    {
        if (!$clientId) return;
        $query->whereHas('jobList.workOrder.client', fn($q) => $q->where('id', $clientId));
    }

    protected function applyAgentApplicationsFilter(Builder $query, ?array $applicationIds): void
    {
        if (empty($applicationIds)) return;
        $query->whereIn('id', $applicationIds);
    }

    protected function applyAgentFilter(Builder $query, ?int $agentId): void
    {
        if (!$agentId) return;
        $query->whereHas('agent', fn($q) => $q->where('id', $agentId));
    }

    protected function applyPaymentResponsibilityFilter(Builder $query, mixed $payer): void
    {
        if ($payer === null || $payer === '') {
            return;
        }

        $payers = is_array($payer) ? $payer : [$payer];
        $payers = array_values(array_filter(array_map(
            static fn ($item) => strtolower(trim((string) $item)),
            $payers
        )));

        if (empty($payers)) {
            return;
        }

        $query->where(function (Builder $builder) use ($payers) {
            foreach ($payers as $index => $type) {
                if ($index === 0) {
                    $builder->whereJsonContains('payment_responsibility', $type);
                } else {
                    $builder->orWhereJsonContains('payment_responsibility', $type);
                }
            }
        });
    }

    protected function applyWorkOrderFilter(Builder $query, ?int $workOrderId): void
    {
        if (!$workOrderId) return;
        $query->whereHas('jobList.workOrder', fn($q) => $q->where('id', $workOrderId));
    }
    public function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) return;

        $search = trim($search);
        $columns = ['sur_name', 'given_name', 'email', 'mobile', 'application_id', 'passport_no', 'nid_no'];

        $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $col) {
                $q->orWhere($col, 'like', "%{$search}%");
            }

            // Search in jobList
            $q->orWhereHas(
                'jobList',
                fn($q2) =>
                $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('job_code', 'like', "%{$search}%")
            );
        });
    }

    protected function applyJobListFilter(Builder $query, ?int $jobListId): void
    {
        if (!$jobListId) return;
        $query->where('job_list_id', $jobListId);
    }

    protected function applyProcessFilter(Builder $query, ?int $processId): void
    {
        if (!$processId) return;

        if ($processId === $this->processService->getHiringListProcessId()) {
            $this->applyNoProcess($query);
        } else {
            $this->applyCurrentProcess($query, $processId);
        }
    }

    protected function applyApplicationStatusFilter(Builder $query, mixed $status): void
    {
        if (!$status) return;

        $statuses = is_array($status) ? $status : [$status];
        $statuses = array_values(array_filter(array_map(static fn($item) => strtolower((string) $item), $statuses)));

        if (empty($statuses)) return;

        $query->whereIn(DB::raw('LOWER(application_status)'), $statuses);
    }

    protected function applyCurrentProcess(Builder $query, int $processId): void
    {
        $query->whereHas(
            'currentProcess',
            fn($q) =>
            $q->where('process_id', $processId)
        );
    }

    protected function applyNoProcess(Builder $query): void
    {
        $query->whereDoesntHave('processes');
    }

    protected function applyDateRange(Builder $query, ?string $fromDate, ?string $toDate): void
    {
        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        if ($this->usesListPageOrdering($filters)) {
            $query->leftJoin('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
                ->select('applications.*')
                ->orderByDesc('job_lists.job_code')
                ->orderBy('applications.given_name');

            return;
        }

        $query->orderByDesc('id');
    }

    private const LIST_PAGE_STATUSES = [
        'hiring_list',
        'application_list',
        'short_list',
        'waiting_list',
        'rejected_list',
    ];

    private function usesListPageOrdering(array $filters): bool
    {
        $status = $filters['application_status'] ?? null;

        if (!$status) {
            return false;
        }

        $statuses = is_array($status) ? $status : [$status];

        foreach ($statuses as $item) {
            $normalized = strtolower((string) $item);

            if (in_array($normalized, self::LIST_PAGE_STATUSES, true)) {
                return true;
            }
        }

        return false;
    }
}
