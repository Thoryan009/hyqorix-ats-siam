<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use App\Services\BaseCachedService;
use App\Modules\Auth\Repositories\PermissionRepository;

class PermissionService extends BaseCachedService
{
    public function __construct(protected PermissionRepository $repository)
    {
        parent::__construct(new Permission());
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

    public function getPermission(Permission $permission)
    {
        return $this->remember(
            $this->byIdCacheKey($permission->id),
            fn () => $permission
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createPermission(array $data)
    {
        return $this->mutate(fn () => $this->model->create($data));
    }

    /**
     * Quickly create standard module permissions (module.create, module.edit, ...).
     *
     * @param  array{module: string, actions: list<string>, label?: string, assign_to_admin?: bool}  $data
     * @return array{created: list<\App\Modules\Auth\Models\Permission>, skipped: list<\App\Modules\Auth\Models\Permission>}
     */
    public function createModulePermissions(array $data): array
    {
        return $this->mutate(function () use ($data) {
            $module = (string) $data['module'];
            $actions = $data['actions'] ?? [];
            $label = trim((string) ($data['label'] ?? '')) ?: str_replace('_', ' ', $module);
            $assignToAdmin = (bool) ($data['assign_to_admin'] ?? true);

            $created = [];
            $skipped = [];
            $permissionIds = [];

            foreach ($actions as $action) {
                $slug = "{$module}.{$action}";
                $name = ucwords(str_replace('_', ' ', $action)).' '.ucwords($label);

                $existing = $this->model->newQuery()->where('slug', $slug)->first();
                if ($existing) {
                    $skipped[] = $existing;
                    $permissionIds[] = $existing->id;
                    continue;
                }

                $permission = $this->model->create([
                    'name' => $name,
                    'slug' => $slug,
                ]);
                $created[] = $permission;
                $permissionIds[] = $permission->id;
            }

            if ($assignToAdmin && $permissionIds !== []) {
                $admin = Role::query()->where('slug', 'admin')->first();
                if ($admin) {
                    $admin->permissions()->syncWithoutDetaching($permissionIds);
                }
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
            ];
        });
    }

    public function updatePermission(Permission $permission, array $data)
    {
        return $this->mutate(fn () => tap($permission)->update($data));
    }

    public function deletePermission(Permission $permission): bool
    {
        return $this->mutate(fn () => $permission->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn () => $this->model->whereIn('id', $ids)->delete());
    }
}
