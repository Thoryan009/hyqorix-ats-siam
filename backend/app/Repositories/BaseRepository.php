<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasDateFilter;

abstract class BaseRepository
{
    use HasDateFilter;

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function baseQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Paginate data with filters
     */
    public function getPaginatedData(array $filters = [])
    {
        $query = $this->baseQuery();
        
        // Apply module-specific filters
        $data =  $this->applyFilters($query, $filters);

        // Apply eager loads
        $this->applyEagerLoads($query, $filters);

        // Apply generic ordering (default id desc)
        $this->applyOrder($query, $filters);

        return $query->paginate(
            $filters['per_page'] ?? 10,
            ['*'],
            'page',
            $filters['page'] ?? 1
        );
    }

    /**
     * Fetch all matching data with filters (no pagination).
     *
     * Useful for exports where the backend should return the full dataset.
     */
    public function getAllData(array $filters = [])
    {
        $query = $this->baseQuery();

        // Apply module-specific filters
        $this->applyFilters($query, $filters);

        // Apply eager loads
        $this->applyEagerLoads($query, $filters);

        // Apply ordering
        $this->applyOrder($query, $filters);

        return $query->get();
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        if (isset($filters['with']) && is_array($filters['with'])) {
            $query->with($filters['with']);
        }
    }

    /**
     * Module-specific filters (must be implemented in child)
     */
    abstract protected function applyFilters(Builder $query, array $filters): void;

    /**
     * Default ordering (id desc)
     * Open/Closed: child can override if custom ordering is needed
     */
    protected function applyOrder(Builder $query, array $filters): void
{
    $allowedSortColumns = ['id', 'name', 'created_at', 'updated_at'];

    $sortBy = $filters['sort_by'] ?? 'id';
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'id';
    }

    $sortDirection = $filters['sort_direction'] ?? 'desc';
    if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
        $sortDirection = 'desc';
    }

    $query->orderBy($sortBy, $sortDirection);
}

}
