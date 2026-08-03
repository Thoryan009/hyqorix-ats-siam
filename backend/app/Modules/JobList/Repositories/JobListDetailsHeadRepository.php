<?php

namespace App\Modules\JobList\Repositories;

use App\Modules\JobList\Models\JobListDetailsHead;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class JobListDetailsHeadRepository extends BaseRepository
{
    public function __construct(JobListDetailsHead $model)
    {
        parent::__construct($model);
    }

    public function baseQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('jobListDetailsCategory');
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyCategoryFilter($query, $filters['category_id'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) return;

        $query->where('name', 'like', "%{$search}%");
    }

    protected function applyCategoryFilter(Builder $query, ?int $categoryId): void
    {
        if (!$categoryId) return;

        $query->where('job_list_details_category_id', $categoryId);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('name', 'asc');
    }
}
