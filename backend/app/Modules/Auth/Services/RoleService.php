<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\Role;
use App\Services\BaseCachedService;
use App\Modules\Auth\Repositories\RoleRepository;
use Illuminate\Validation\ValidationException;

class RoleService extends BaseCachedService
{
    public const PROTECTED_SLUGS = ['admin'];

    public function __construct(protected RoleRepository $repository)
    {
        parent::__construct(new Role());
    }

    public function isProtectedRole(Role $role): bool
    {
        return in_array(strtolower((string) $role->slug), self::PROTECTED_SLUGS, true);
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

    public function getRole(Role $role)
    {
        return $this->remember(
            $this->byIdCacheKey($role->id),
            fn () => $role
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createRole(array $data)
    {
        $role = $this->mutate(fn () => $this->model->create($data));
        return $role;
    }

    public function updateRole(Role $role, array $data)
    {
        return $this->mutate(fn () => tap($role)->update($data));
    }

    public function updateRolePermissions(Role $role, array $permission_ids)
    {
        return $this->mutate(fn () => tap($role)->permissions()->sync($permission_ids));
    }

    public function deleteRole(Role $role): bool
    {
        if ($this->isProtectedRole($role)) {
            throw ValidationException::withMessages([
                'role' => ['The Admin role cannot be deleted.'],
            ]);
        }

        return $this->mutate(fn () => $role->delete());
    }

    public function bulkDelete(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return 0;
        }

        $protectedIds = $this->model->newQuery()
            ->whereIn('id', $ids)
            ->whereIn('slug', self::PROTECTED_SLUGS)
            ->pluck('id')
            ->all();

        if ($protectedIds !== []) {
            throw ValidationException::withMessages([
                'ids' => ['The Admin role cannot be deleted.'],
            ]);
        }

        return $this->mutate(fn () => $this->model->whereIn('id', $ids)->delete());
    }
}
