<?php

namespace App\Modules\__PARENT_MODEL__\Repositories;

use App\Modules\__PARENT_MODEL__\Models\__MODEL__;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class __MODEL__Repository extends BaseRepository
{
    public function __construct(__MODEL__ $model)
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
