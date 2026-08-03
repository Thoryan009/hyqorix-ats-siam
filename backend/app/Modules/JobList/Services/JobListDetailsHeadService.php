<?php

namespace App\Modules\JobList\Services;

use App\Modules\JobList\Models\JobListDetailsCategory;
use App\Modules\JobList\Models\JobListDetailsHead;
use App\Services\BaseCachedService;
use App\Modules\JobList\Repositories\JobListDetailsHeadRepository;

class JobListDetailsHeadService extends BaseCachedService
{
    public function __construct(protected JobListDetailsHeadRepository $repository)
    {
        parent::__construct(new JobListDetailsHead());
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
     public function getById(int $id)
    {
       return $this->remember(
            $this->byIdCacheKey($id),
            fn() => $this->model->findOrFail($id)
        );
    }

    public function getJobListHeadCategories()
    {
        return JobListDetailsCategory::select('id', 'name')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            });
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getJobListDetailsHead(JobListDetailsHead $jobListDetailsHead)
    {
        return $this->remember(
            $this->byIdCacheKey($jobListDetailsHead->id),
            fn () => $jobListDetailsHead
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createJobListDetailsHead(array $data)
    {
        $jobListDetailsHead = $this->mutate(fn() => $this->model->create($data));
        return $jobListDetailsHead;
    }

    public function updateJobListDetailsHead(JobListDetailsHead $jobListDetailsHead, array $data)
    {
        return $this->mutate(fn() => tap($jobListDetailsHead)->update($data));
    }

    public function deleteJobListDetailsHead(JobListDetailsHead $jobListDetailsHead): bool
    {
        return $this->mutate(fn() => $jobListDetailsHead->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
