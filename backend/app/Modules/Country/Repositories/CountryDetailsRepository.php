<?php

namespace App\Modules\Country\Repositories;

use App\Modules\Country\Models\CountryDetails;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class CountryDetailsRepository extends BaseRepository
{
    public function __construct(CountryDetails $model)
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
