<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\Qualification;
use App\Services\BaseCachedService;
use App\Modules\Application\Repositories\QualificationRepository;

class QualificationService extends BaseCachedService
{
    public function __construct(protected QualificationRepository $repository)
    {
        parent::__construct(new Qualification());
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

    public function getQualification(Qualification $qualification)
    {
        return $this->remember(
            $this->byIdCacheKey($qualification->id),
            fn () => $qualification
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createQualification(array $data)
    {
        $qualification = $this->mutate(fn() => $this->model->create($data));
        return $qualification;
    }

    public function updateQualification(Qualification $qualification, array $data)
    {
        return $this->mutate(fn() => tap($qualification)->update($data));
    }

    public function deleteQualification(Qualification $qualification): bool
    {
        return $this->mutate(fn() => $qualification->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
