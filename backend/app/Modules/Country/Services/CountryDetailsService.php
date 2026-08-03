<?php

namespace App\Modules\Country\Services;

use App\Modules\Country\Models\CountryDetails;
use App\Services\BaseCachedService;
use App\Modules\Country\Repositories\CountryDetailsRepository;

class CountryDetailsService extends BaseCachedService
{
    public function __construct(protected CountryDetailsRepository $repository)
    {
        parent::__construct(new CountryDetails());
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

    public function getCountryDetails(CountryDetails $countryDetails)
    {
        return $this->remember(
            $this->byIdCacheKey($countryDetails->id),
            fn () => $countryDetails
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createCountryDetails(array $data)
    {
        $countryDetails = $this->mutate(fn() => $this->model->create($data));
        return $countryDetails;
    }

    public function updateCountryDetails(CountryDetails $countryDetails, array $data)
    {
        return $this->mutate(fn() => tap($countryDetails)->update($data));
    }

    public function deleteCountryDetails(CountryDetails $countryDetails): bool
    {
        return $this->mutate(fn() => $countryDetails->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
