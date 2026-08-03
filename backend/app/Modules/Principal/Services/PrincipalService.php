<?php

namespace App\Modules\Principal\Services;

use Illuminate\Support\Facades\DB;
use App\Services\BaseCachedService;
use App\Modules\Principal\Models\Principal;
use App\Modules\Principal\Repositories\PrincipalRepository;

class PrincipalService extends BaseCachedService
{
    public function __construct(protected PrincipalRepository $repository)
    {
        parent::__construct(new Principal());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn() => $this->repository->getPaginatedData($filters)
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById(int $id)
    {
        return $this->model->findOrFail($id);
    }
    public function getPrincipal(Principal $principal)
    {
        return $this->remember(
            $this->byIdCacheKey($principal->id),
            fn() => $principal
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createPrincipal(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['principal_id'] = $this->generatePrincipalId();
            $user = $this->repository->createUser($data);
            $principal = $this->repository->createPrincipal($user->id, $data);
             $this->repository->assignRoles($user, $data['role_id']);
            $this->flushCache();
            return $principal;
        });
    }

    public function updatePrincipal(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $principal = $this->model->with('user')->findOrFail($id);
            $this->repository->updateUser($principal->user, $data, $principal);
            $principal = $this->repository->updatePrincipal($principal, $data);
             $this->repository->assignRoles($principal->user, $data['role_id']);
            $this->flushCache();
            return $principal;
        });
    }

    public function deletePrincipal(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $principal = $this->model->with('user')->findOrFail($id);
            $principal->user?->delete();
            $principal->delete();
            $this->flushCache();
            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $principals = $this->model->whereIn('id', $ids)->get();
            foreach ($principals as $principal) {
                $principal->user?->delete();
                $principal->delete();
            }
            $this->flushCache();
            return count($principals);
        });
    }
    public function generatePrincipalId(): string
    {
        $lastPrincipal = $this->model->orderBy('id', 'desc')->first();
        $lastNumber = $lastPrincipal ? (int) str_replace('PRINCIPAL-', '', $lastPrincipal->principal_id) : 0;

        $newNumber = $lastNumber + 1;

        return 'PRINCIPAL-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
