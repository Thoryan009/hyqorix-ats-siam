<?php

namespace App\Modules\Application\Services;

use Illuminate\Support\Facades\Cache;
use App\Modules\Application\Models\Process;
use App\Services\BaseCachedService;
use App\Modules\Application\Repositories\ProcessRepository;

class ProcessService extends BaseCachedService
{
    // protected Process $model;

    public function __construct(protected ProcessRepository $repository)
    {
        $this->model = new Process();
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(
        int $page = 1,
        int $perPage = 10,
        array $filters = []
    ) {

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

    public function getProcessIdByName(string $name)
    {
        $cacheKey = $this->getProcessCacheKey($name);
        return Cache::tags($this->getCacheTag())->remember(
            $cacheKey,
            $this->getCacheTtl(),
            fn() => $this->model->where('name', $name)->value('id')
        );
    }
    public function update(int $id, array $data)
    {
        return $this->mutate(fn() => tap($this->model->findOrFail($id))->update($data));
    }

    protected function getProcessCacheKey(string $name): string
    {
        return $this->getCacheTag() . "_by_name_{$name}";
    }

    public function getHiringListProcessId(): int
    {
        return $this->model->count() + 1;
    }

    public function getRejectedProcessId(): int
    {
         return config('app.process_rejected_id', 15);
    }

    public function getDeclinedProcessId(): int
    {
        return config('app.process_declined_id', 16);
    }
}
