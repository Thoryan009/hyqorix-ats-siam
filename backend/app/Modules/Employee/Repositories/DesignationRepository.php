<?php
namespace App\Modules\Employee\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\Employee\Models\Designation;
use App\Repositories\BaseRepository;
class DesignationRepository extends BaseRepository
{
    public function __construct(Designation $model)
    {
        $this->model = $model;
    }
    /**
     * Apply all filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    /**
     * Search by 'name' field
     */
    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) return;
        $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Apply from/to date filters
     */

}

