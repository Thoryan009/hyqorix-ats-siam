<?php

namespace App\Modules\JobList\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Repositories\BaseRepository;
use App\Modules\JobList\Models\JobList;

class AtsRepository extends BaseRepository
{
    public function __construct(JobList $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedData(array $filters = [])
    {
        $query = $this->baseQuery();

        // active job query
        $this->applyActiveJobFilter($query);

        //joblist applications must have at least 1 current process to be included in ATS listing
        $query->whereHas('applications.currentProcess');

        // Module filters
        $this->applyFilters($query, $filters);

        // ⭐ Apply ATS counts
        $query = $this->applyProcessCounts($query);

        // Generic ordering
        $this->applyOrder($query, $filters);

        return $query->paginate($filters['per_page'] ?? 10, ['*'], 'page', $filters['page'] ?? 1);
    }

    /**
     * Filter to only include jobs with active applications
     */
    protected function applyActiveJobFilter(Builder $query): void
    {
        $query->where('status', 'open');
    }

    /**
     * Apply all supported filters to job list query
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyWorkOrderFilter($query, $filters['work_order_id'] ?? null);
        $this->applyJobListFilter($query, $filters['job_list_id'] ?? null);
        $this->applyAgentJobListFilter($query, $filters['job_list_ids'] ?? null);
        $this->applyClientFilter($query, $filters['client_id'] ?? null);
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

        $query
            ->where('name', 'like', "%{$search}%")
            ->orWhere('job_code', 'like', "%{$search}%")
            ->orWhereHas('workOrder', function (Builder $q2) use ($search) {
                $q2->where('work_order_id', 'like', "%{$search}%");
            });
    }

    /**
     * Exact filter by work_order_id
     */
    public function applyWorkOrderFilter(Builder $query, ?string $workOrderId): Builder
    {
        return $query->when($workOrderId, fn($q) => $q->where('work_order_id', $workOrderId));
    }

    public function applyJobListFilter(Builder $query, ?int $jobListId): Builder
    {
        return $query->when($jobListId, fn($q) => $q->where('id', $jobListId));
    }

    public function applyAgentJobListFilter(Builder $query, ?array $jobListIds): Builder
    {
        return $query->when($jobListIds, fn($q) => $q->whereIn('id', $jobListIds));
    }

    /**
     * Exact filter by client_id
     */
    public function applyClientFilter(Builder $query, ?int $clientId): Builder
    {
        return $query->when($clientId, fn($q) => $q->whereHas('workOrder', fn($wq) => $wq->where('client_id', $clientId)));
    }

    /**
     * All ATS process names
     */
    protected array $atsProcesses = ['offer_extended', 'visa_authorization', 'medical_test', 'police_clearance', 'trade_test', 'biometric_enrollment', 'embassy_submission', 'bmet_training', 'bmet_biometric_enrollment', 'immigration_clearance', 'pta_request', 'onboarding', 'tra_process'];

    /**
     * Apply ATS process-wise application counts
     */
    public function applyProcessCounts(Builder $query): Builder
    {
        $withCounts = ['applications'];

        foreach ($this->atsProcesses as $process) {
            $withCounts["applications as {$process}"] = fn($q) => $q->whereHas('processes.process', fn($qq) => $qq->where('name', $process));
        }

        return $query->withCount($withCounts);
    }

    public function getJobWithApplications(?int $jobId = null, ?int $processId = null, ?int $applicationId = null)
    {
        $query = $this->buildJobBaseQuery($jobId);

        $this->loadAllApplications($query);
        $this->loadFilteredApplications($query, $processId, $applicationId);
        $this->applyApplicationCounts($query);

        return $query->get();
    }

    protected function buildJobBaseQuery(?int $jobId)
    {
        return $this->model->newQuery()->when($jobId, fn($q) => $q->where('id', $jobId));
    }

    protected function loadAllApplications($query): void
    {
        $query->with(['applications.processes.process']);
    }

    protected function loadFilteredApplications($query, ?int $processId, ?int $applicationId = null): void
    {
        $query->with([
            'filteredApplications' => function ($q) use ($processId, $applicationId) {
                if (!$processId && !$applicationId) {
                    return;
                }

                $q->where(function ($group) use ($processId, $applicationId) {
                    if ($processId) {
                        if ($processId == config('app.process_rejected_id', 15)) {
                            $group->whereHas('currentProcess', function ($qq) {
                                $qq->where('status', 'rejected');
                            });
                        } elseif ($processId == config('app.process_declined_id', 16)) {
                            $group->whereHas('currentProcess', function ($qq) {
                                $qq->where('status', 'declined');
                            });
                        } elseif ($processId == config('app.process_deployed_id', 17)) {
                            $group->whereHas('currentProcess', function ($qq) {
                                $qq->where('status', 'completed')->whereHas('process', function ($qqq) {
                                    $qqq->where('name', 'on_boarding');
                                });
                            });
                        } else {
                            $group->whereHas('currentProcess', function ($qq) use ($processId) {
                                $qq->where('process_id', $processId)
                                    ->whereNotIn('status', ['rejected', 'declined'])
                                    ->where(function ($q) {
                                        $q->whereHas('process', fn($p) => $p->where('name', '!=', 'on_boarding'))->orWhere(function ($q2) {
                                            $q2->whereHas('process', fn($p) => $p->where('name', 'on_boarding'))->where('status', '!=', 'completed');
                                        });
                                    });
                            });
                        }
                    }

                    if ($applicationId) {
                        $group->orWhere('id', $applicationId);
                    }
                });
            },
        ]);
    }

    protected function applyApplicationCounts($query): void
    {
        $query->withCount('applications');
    }
}
