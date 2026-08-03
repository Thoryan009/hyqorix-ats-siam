<?php

namespace App\Modules\JobList\Repositories;

use App\Modules\JobList\Models\JobListDetailsCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class JobListDetailsCategoryRepository extends BaseRepository
{
    public function __construct(JobListDetailsCategory $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) return;

        $query->where('name', 'like', "%{$search}%");
    }
}
