<?php

namespace App\Modules\JobList\Repositories;

use App\Modules\Agent\Models\Agent;
use App\Modules\Application\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\JobList\QueryBuilders\AtsQueryBuilder;
use App\Repositories\BaseRepository;
use App\Modules\JobList\Models\JobList;


class JobListRepository extends BaseRepository
{
    public function __construct(protected AtsQueryBuilder $atsQueryBuilder, JobList $model)
    {
        parent::__construct($model);
    }



    /**
     * Apply all supported filters to job list query
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyWorkOrderIdFilter($query, $filters['work_order_id'] ?? null);
        $this->applyPrincipalIdFilter($query, $filters['principal_id'] ?? null);
        $this->applyClientIdFilter($query, $filters['client_id'] ?? null);
        $this->applyAgentIdFilter($query, $filters['agent_id'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyHasAtsApplicationsFilter($query, $filters['has_ats_applications'] ?? null);
        $this->applyAtsApplicationsCount($query, $filters['include_ats_count'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    /**
     * Partial search by work_order_id
     */
    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where('name', 'like', "%{$search}%")
            ->orWhereHas('workOrder', function (Builder $q2) use ($search) {
                $q2->where('work_order_id', 'like', "%{$search}%");
            });
    }

    /**
     * Exact filter by work_order_id
     */
    protected function applyWorkOrderIdFilter(
        Builder $query,
        ?string $workOrderId
    ): void {
        if (!$workOrderId) {
            return;
        }

        $query->where('work_order_id', $workOrderId);
    }

    /**
     * Exact filter by client_id
     */
    protected function applyClientIdFilter(
        Builder $query,
        ?int $clientId
    ): void {
        if (!$clientId) {
            return;
        }

        $query->whereHas('workOrder', function (Builder $q) use ($clientId) {
            $q->where('client_id', $clientId);
        });
    }

    /**
     * Exact filter by agent_id
     */
    protected function applyAgentIdFilter(
        Builder $query,
        ?int $agentId
    ): void {
        if (!$agentId) {
            return;
        }

        $query->whereIn('id', function ($q) use ($agentId) {
            $q->select('job_list_id')
                ->from('applications')
                ->where('agent_id', $agentId);
        });
    }

    protected function applyPrincipalIdFilter(
        Builder $query,
        ?int $principalId
    ): void {
        if (!$principalId) {
            return;
        }

        $query->where('principal_id', $principalId);
    }

    protected function applyStatusFilter(Builder $query, ?string $status): void
    {
        if (!$status) {
            return;
        }

        $query->where('status', $status);
    }

    protected function applyHasAtsApplicationsFilter(Builder $query, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $query->whereHas('applications', function (Builder $applicationQuery) {
            $applicationQuery->whereRaw("UPPER(application_status) = 'ATS'");
        });
    }

    protected function applyAtsApplicationsCount(Builder $query, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $query->withCount([
            'applications as ats_applications_count' => function (Builder $applicationQuery) {
                $applicationQuery->whereRaw("UPPER(application_status) = 'ATS'");
            },
        ]);
    }

}
