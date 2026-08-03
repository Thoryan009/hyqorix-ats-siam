<?php

namespace App\Modules\__PARENT_MODEL__\Services;

use App\Modules\__PARENT_MODEL__\Models\__MODEL__;
use App\Services\BaseCachedService;
use App\Modules\__PARENT_MODEL__\Repositories\__MODEL__Repository;

class __MODEL__Service extends BaseCachedService
{
    public function __construct(protected __MODEL__Repository $repository)
    {
        parent::__construct(new __MODEL__());
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

    public function get__MODEL__(__MODEL__ $__MODEL_VAR__)
    {
        return $this->remember(
            $this->byIdCacheKey($__MODEL_VAR__->id),
            fn () => $__MODEL_VAR__
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function create__MODEL__(array $data)
    {
        $__MODEL_VAR__ = $this->mutate(fn() => $this->model->create($data));
        return $__MODEL_VAR__;
    }

    public function update__MODEL__(__MODEL__ $__MODEL_VAR__, array $data)
    {
        return $this->mutate(fn() => tap($__MODEL_VAR__)->update($data));
    }

    public function delete__MODEL__(__MODEL__ $__MODEL_VAR__): bool
    {
        return $this->mutate(fn() => $__MODEL_VAR__->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
