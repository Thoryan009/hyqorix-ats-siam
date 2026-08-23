<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\ChartOfAccount;
use App\Modules\Accounts\Repositories\ChartOfAccountRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ChartOfAccountService
{
    public function __construct(
        protected ChartOfAccountRepository $repository,
        protected ChartOfAccount $model,
    ) {}

    public function getPaginatedData(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getPaginatedData($filters);
    }

    public function getChartOfAccount(ChartOfAccount $chartOfAccount): ChartOfAccount
    {
        return $chartOfAccount->load(['createdBy:id,name', 'updatedBy:id,name']);
    }

    public function create(array $data): ChartOfAccount
    {
        return $this->model->create($data);
    }

    public function update(ChartOfAccount $chartOfAccount, array $data): ChartOfAccount
    {
        $chartOfAccount->update($data);

        return $chartOfAccount->fresh(['createdBy:id,name', 'updatedBy:id,name']);
    }

    public function delete(ChartOfAccount $chartOfAccount): bool
    {
        return (bool) $chartOfAccount->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}
