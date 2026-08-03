<?php

namespace App\Modules\Application\Repositories;

use App\Modules\Application\Models\Subject;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class SubjectRepository extends BaseRepository
{
    public function __construct(Subject $model)
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

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('name', 'asc');
    }
}
