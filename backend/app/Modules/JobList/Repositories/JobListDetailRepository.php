<?php

namespace App\Modules\JobList\Repositories;

use App\Modules\JobList\Models\JobListDetail;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class JobListDetailRepository extends BaseRepository
{
    public function __construct(JobListDetail $model)
    {
        parent::__construct($model);
    }

    public function baseQuery(): Builder
    {
        return $this->model
            ->newQuery()
            ->with(['jobList', 'jobListDetailsHead'])
            ->orderBy('id', 'desc');
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applyJobListFilter($query, $filters['job_list_id'] ?? null);
        $this->applyJobListDetailsHeadFilter($query, $filters['job_list_details_head_id'] ?? null);
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applyJobListFilter(Builder $query, ?int $jobListId): void
    {
        if (!$jobListId) {
            return;
        }
        $query->where('job_list_id', $jobListId);
    }

    protected function applyJobListDetailsHeadFilter(Builder $query, ?int $jobListDetailsHeadId): void
    {
        if (!$jobListDetailsHeadId) {
            return;
        }
        $query->where('job_list_details_head_id', $jobListDetailsHeadId);
    }

  protected function applySearch(Builder $query, ?string $search): void
{
    if (!$search) return;


    $query->where(function ($q) use ($search) {
            $q->whereHas('jobListDetailsHead', fn($q2) =>
                $q2->where('name', 'like', "%{$search}%")
            )
            ->orWhereHas('jobListDetailsHead.jobListDetailsCategory', fn($q3) =>
                $q3->where('name', 'like', "%{$search}%")
            );
    });
}
}
