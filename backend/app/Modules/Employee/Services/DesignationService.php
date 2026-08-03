<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\Designation;
use App\Services\BaseCachedService;
use App\Modules\Employee\Repositories\DesignationRepository;


class DesignationService extends BaseCachedService
{


    public function __construct(protected DesignationRepository $repository)
    {
         parent::__construct(new Designation());
    }

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

    /**
     * Get a single record by ID with cache.
     */
    public function getById(int $id)
    {
         return $this->remember(
            $this->byIdCacheKey($id),
            fn() => $this->model->findOrFail($id)
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function create(array $data)
    {
        $record = $this->model->create($data);
        $this->flushCache();
        return $record;
    }

    public function update(int $id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        $this->flushCache();
        return $record;
    }

    public function delete(int $id): bool
    {
        $record = $this->model->findOrFail($id);
        $deleted = $record->delete();
        $this->flushCache();
        return $deleted;
    }

    public function bulkDelete(array $ids): int
    {
        $deletedCount = $this->model->whereIn('id', $ids)->delete();
        $this->flushCache();
        return $deletedCount;
    }


}
