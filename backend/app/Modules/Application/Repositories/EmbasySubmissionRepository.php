<?php

namespace App\Modules\Application\Repositories;

use App\Modules\Application\Models\Application;
use App\Modules\Application\Models\EmbasySubmission;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class EmbasySubmissionRepository extends BaseRepository
{
    public function __construct(EmbasySubmission $model)
    {
        parent::__construct($model);
    }

    protected function baseQuery(): Builder
    {
        return Application::query()->whereHas('processes', function ($query) {
            $query->whereJsonContains('data->medical_fit', 'fit');
        });
        // ->where(function ($query) {
        //     $query->doesntHave('embassySubmission')
        //         ->orWhereHas('embassySubmission', function ($q) {
        //             $q->whereNull('ksa_visa_status');
        //         });
        // });
        // this condition has been applied in the last function of this file
    }
    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyJobListFilter($query, $filters['job_list_id'] ?? null);
        $this->applyWorkOrderFilter($query, $filters['work_order_id'] ?? null);
        $this->applyClientFilter($query, $filters['client_id'] ?? null);
        $this->applyAgentFilter($query, $filters['agent_id'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyHasEmbassySubmissionFilter($query, $filters['has_embassy_submission'] ?? null);
        $this->applyAgentApplicationsFilter($query, $filters['application_ids'] ?? null);
        $this->applyCountryFilter($query, $filters['country_id'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applyClientFilter(Builder $query, ?int $clientId): void
    {
        if (!$clientId) {
            return;
        }
        $query->whereHas('jobList.workOrder.client', fn($q) => $q->where('id', $clientId));
    }

    protected function applyCountryFilter(Builder $query, ?int $countryId): void
    {
        if (!$countryId) {
            return;
        }
        $query->whereHas('jobList.workOrder.client', fn($q) => $q->where('country_id', $countryId));
    }

    protected function applyAgentApplicationsFilter(Builder $query, ?array $applicationIds): void
    {
        if (empty($applicationIds)) {
            return;
        }
        $query->whereIn('id', $applicationIds);
    }

    protected function applyAgentFilter(Builder $query, ?int $agentId): void
    {
        if (!$agentId) {
            return;
        }
        $query->whereHas('agent', fn($q) => $q->where('id', $agentId));
    }

    protected function applyWorkOrderFilter(Builder $query, ?int $workOrderId): void
    {
        if (!$workOrderId) {
            return;
        }
        $query->whereHas('jobList.workOrder', fn($q) => $q->where('id', $workOrderId));
    }
    public function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);
        $columns = ['sur_name', 'given_name', 'email', 'mobile', 'application_id', 'passport_no', 'nid_no'];

        $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $col) {
                $q->orWhere($col, 'like', "%{$search}%");
            }

            // Search in jobList
            $q->orWhereHas('jobList', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('job_code', 'like', "%{$search}%"));
        });
    }

    protected function applyJobListFilter(Builder $query, ?int $jobListId): void
    {
        if (!$jobListId) {
            return;
        }
        $query->where('job_list_id', $jobListId);
    }

    protected function applyStatusFilter(Builder $query, ?string $status)
    {
        // : void
        if (!$status || $status === 'pending') {
            // Default pending data
            $query->where(function ($q) {
                $q->doesntHave('embassySubmission')->orWhereHas('embassySubmission', function ($subQ) {
                    $subQ->whereNull('ksa_visa_status');
                });
            });

            return;
        }

        $query->whereHas('embassySubmission', function ($q) use ($status) {
            $q->where('ksa_visa_status', $status);
        });
    }
    protected function applyHasEmbassySubmissionFilter(Builder $query, ?string $hasEmbassySubmission): void
    {
        if (!$hasEmbassySubmission) {
            return;
        }

        if ($hasEmbassySubmission === 'yes') {
            $query->whereHas('embassySubmission', function ($q) {
                $q->whereNotNull('mofa_no');
            });
        } elseif ($hasEmbassySubmission === 'no') {
            $query->doesntHave('embassySubmission')
            ->orWhere(function ($q) {
                $q->whereHas('embassySubmission', function ($subQ) {
                    $subQ->whereNull('mofa_no');
                });
            });
        }
    }
}
