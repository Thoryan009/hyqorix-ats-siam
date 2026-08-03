<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\Subject;
use App\Services\BaseCachedService;
use App\Modules\Application\Repositories\SubjectRepository;

class SubjectService extends BaseCachedService
{
    public function __construct(protected SubjectRepository $repository)
    {
        parent::__construct(new Subject());
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

    public function getSubject(Subject $subject)
    {
        return $this->remember(
            $this->byIdCacheKey($subject->id),
            fn () => $subject
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createSubject(array $data)
    {
        $subject = $this->mutate(fn() => $this->model->create($data));
        return $subject;
    }

    public function updateSubject(Subject $subject, array $data)
    {
        return $this->mutate(fn() => tap($subject)->update($data));
    }

    public function deleteSubject(Subject $subject): bool
    {
        return $this->mutate(fn() => $subject->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
