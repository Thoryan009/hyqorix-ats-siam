<?php

namespace App\Modules\Country\Repositories;

use App\Modules\Country\Models\Country;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class CountryRepository extends BaseRepository
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }

    /**
     * Module-specific filters
     */
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
