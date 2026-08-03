<?php
namespace App\Modules\WorkOrder\Repositories;

use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\BaseRepository;

class WorkOrderRepository extends BaseRepository
{
    public function __construct(WorkOrder $model)
    {
        $this->model = $model;
    }
    /**
     * Apply all filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyClientFilter($query, $filters['client_id'] ?? null);
        $this->applyEmployeeFilter($query, $filters['employee_id'] ?? null);
        $this->applyHasJobsFilter($query, $filters['has_jobs'] ?? null);
        $this->applyJobsCount($query, $filters['include_jobs_count'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    /**
     * Search work_order_id + related client.user fields
     */
    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('work_order_id', 'like', "%{$search}%")
              ->orWhereHas('client', function (Builder $clientQ) use ($search) {
                  $clientQ->whereHas('user', function (Builder $userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                  });
              });
        });
    }

    /**
     * Filter by client_id
     */
    protected function applyClientFilter(Builder $query, ?int $clientId): void
    {
        if (!$clientId) {
            return;
        }

        $query->where('client_id', $clientId);
    }

    protected function applyEmployeeFilter(Builder $query, ?int $employeeId): void
    {
        if (!$employeeId) {
            return;
        }

        $query->where('employee_id', $employeeId);
    }

    protected function applyHasJobsFilter(Builder $query, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $query->whereHas('jobLists');
    }

    protected function applyJobsCount(Builder $query, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $query->withCount('jobLists as jobs_count')
            ->with(['client.country', 'client.user']);
    }

    /**
     * Filter by created_at dates
     */

}
