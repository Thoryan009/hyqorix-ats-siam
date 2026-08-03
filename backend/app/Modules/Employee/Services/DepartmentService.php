<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\Department;
use App\Services\BaseCachedService;
use App\Modules\Employee\Repositories\DepartmentRepository;

class DepartmentService extends BaseCachedService
{
    public function __construct(protected DepartmentRepository $repository)
    {
        parent::__construct(new Department());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getDepartment(Department $department)
    {
        return $this->remember(
            $this->byIdCacheKey($department->id),
            fn () => $department
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createDepartment(array $data)
    {
        $department = $this->mutate(fn() => $this->model->create($data));
        return $department;
    }

    public function updateDepartment(Department $department, array $data)
    {
        return $this->mutate(fn() => tap($department)->update($data));
    }

    public function deleteDepartment(Department $department): bool
    {
        return $this->mutate(fn() => $department->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
