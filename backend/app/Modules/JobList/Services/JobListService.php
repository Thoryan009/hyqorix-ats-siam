<?php

namespace App\Modules\JobList\Services;

use App\Modules\JobList\Models\JobList;
use App\Events\JobProcessSubmitted;
use App\Events\NextProcessCreated;
use App\Services\BaseCachedService;
use App\Modules\JobList\Repositories\JobListRepository;

class JobListService extends BaseCachedService
{

    public function __construct(protected JobListRepository $repository)
    {
        parent::__construct(new JobList());
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
        $data['job_code'] = $this->generateJobCode();
        return $this->mutate(fn() => $this->model->create($data));
    }

    public function createNextProcess(array $data)
    {
        NextProcessCreated::dispatch($data);
        return true;
    }

    public function updateJobProcess(array $data)
    {
        JobProcessSubmitted::dispatch($data);
        return true;
    }

    public function update(int $id, array $data)
    {
        return $this->mutate(fn() => tap($this->model->findOrFail($id))->update($data));
    }

    public function delete(int $id): bool
    {
        return $this->mutate(fn() => $this->model->findOrFail($id)->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }

    public function clearApplicationProcessCache(): void
    {
        $this->flushCache();
    }

    protected function getJobListWorkOrdersCacheKey(): string
    {
        return $this->getCacheTag() . '_work_orders';
    }

    protected function getJobListClientsCacheKey(): string
    {
        return $this->getCacheTag() . '_clients';
    }

    private function generateJobCode(): string
    {
        $lastJob = $this->model->orderBy('id', 'desc')->first();
        $lastNumber = $lastJob ? (int) str_replace('JOB-', '', $lastJob->job_code) : 0;

        $newNumber = $lastNumber + 1;

        return 'JOB-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function countByIdsAndClient(array $ids, int $clientId): int
    {
        return $this->model
            ->whereIn('id', $ids)
            ->whereHas('workOrder', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })
            ->count();
    }
}
