<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\Transaction;
use App\Services\BaseCachedService;
use App\Modules\Application\Repositories\CandidateBillRepository;
use Illuminate\Support\Facades\Cache;


class CandidateBillService extends BaseCachedService
{

    public function __construct(protected CandidateBillRepository $repository)
    {
        parent::__construct(new Transaction());
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

    protected function remember(string $key, \Closure $callback)
    {
        return Cache::tags('CandidateBill')
            ->remember($key, $this->getCacheTtl(), $callback);
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById(int $id)
    {
        return $this->remember(
            $this->byIdCacheKey($id),
            fn() => $this->model->findOrFail($id)
        );
    }
}
