<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\IncomeCategory;
use App\Modules\Finance\Repositories\IncomeCategoryRepository;
use App\Services\BaseCachedService;

class IncomeCategoryService extends BaseCachedService
{
    public function __construct(protected IncomeCategoryRepository $repository)
    {
        parent::__construct(new IncomeCategory());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getIncomeCategory(IncomeCategory $incomeCategory): IncomeCategory
    {
        return $this->remember(
            $this->byIdCacheKey($incomeCategory->id),
            fn () => $incomeCategory->loadCount('incomeHeads')
        );
    }

    public function updateIncomeCategory(IncomeCategory $incomeCategory, array $data): IncomeCategory
    {
        return $this->mutate(fn () => tap($incomeCategory)->update([
            'description' => $data['description'] ?? null,
        ]));
    }
}
