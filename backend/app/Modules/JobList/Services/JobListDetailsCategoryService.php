<?php

namespace App\Modules\JobList\Services;

use App\Modules\JobList\Models\JobListDetailsCategory;
use App\Services\BaseCachedService;
use App\Modules\JobList\Repositories\JobListDetailsCategoryRepository;

class JobListDetailsCategoryService extends BaseCachedService
{
    public function __construct(protected JobListDetailsCategoryRepository $repository)
    {
        parent::__construct(new JobListDetailsCategory());
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

    public function getJobListDetailsCategory(JobListDetailsCategory $jobListDetailsCategory)
    {
        return $this->remember(
            $this->byIdCacheKey($jobListDetailsCategory->id),
            fn () => $jobListDetailsCategory
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createJobListDetailsCategory(array $data)
    {
        $jobListDetailsCategory = $this->mutate(fn() => $this->model->create($data));
        return $jobListDetailsCategory;
    }

    public function updateJobListDetailsCategory(JobListDetailsCategory $jobListDetailsCategory, array $data)
    {
        return $this->mutate(fn() => tap($jobListDetailsCategory)->update($data));
    }

    public function deleteJobListDetailsCategory(JobListDetailsCategory $jobListDetailsCategory): bool
    {
        return $this->mutate(fn() => $jobListDetailsCategory->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
