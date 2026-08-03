<?php

namespace App\Modules\Country\Services;

use App\Modules\Country\Models\Country;
use App\Services\BaseCachedService;
use App\Modules\Country\Repositories\CountryRepository;

class CountryService extends BaseCachedService
{
    public function __construct(protected CountryRepository $repository)
    {
        parent::__construct(new Country());
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
        return $this->mutate(fn() => $this->model->create($data));
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

        public function getByIds(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->get(['id', 'country_image_path']); // only needed fields
    }
}
