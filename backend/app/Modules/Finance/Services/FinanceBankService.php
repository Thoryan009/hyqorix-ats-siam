<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinanceBank;
use App\Modules\Finance\Repositories\FinanceBankRepository;
use App\Services\BaseCachedService;

class FinanceBankService extends BaseCachedService
{
    public function __construct(protected FinanceBankRepository $repository)
    {
        parent::__construct(new FinanceBank());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getFinanceBank(FinanceBank $financeBank): FinanceBank
    {
        return $this->remember(
            $this->byIdCacheKey($financeBank->id),
            fn () => $financeBank
        );
    }

    public function createFinanceBank(array $data): FinanceBank
    {
        return $this->mutate(fn () => $this->model->create($data));
    }

    public function updateFinanceBank(FinanceBank $financeBank, array $data): FinanceBank
    {
        return $this->mutate(fn () => tap($financeBank)->update($data));
    }

    public function deleteFinanceBank(FinanceBank $financeBank): bool
    {
        return $this->mutate(fn () => (bool) $financeBank->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn () => $this->model->whereIn('id', $ids)->delete());
    }
}
