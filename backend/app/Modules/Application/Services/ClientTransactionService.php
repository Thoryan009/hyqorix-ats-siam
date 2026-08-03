<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\ClientTransaction;
use App\Services\BaseCachedService;
use App\Modules\Application\Repositories\ClientTransactionRepository;

class ClientTransactionService extends BaseCachedService
{
    public function __construct(protected ClientTransactionRepository $repository)
    {
        parent::__construct(new ClientTransaction());
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

    public function getClientTransaction(ClientTransaction $clientTransaction)
    {
        return $this->remember(
            $this->byIdCacheKey($clientTransaction->id),
            fn () => $clientTransaction
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createClientTransaction(array $data)
    {
        $clientTransaction = $this->mutate(fn() => $this->model->create($data));
        return $clientTransaction;
    }

    public function updateClientTransaction(ClientTransaction $clientTransaction, array $data)
    {
        return $this->mutate(fn() => tap($clientTransaction)->update($data));
    }

        public function updateStatusByBillNo(string $billNo, string $status)
    {
        return $this->mutate(fn() => $this->model->where('bill_no', $billNo)->update(['status' => $status]));
    }

    public function deleteClientTransaction(ClientTransaction $clientTransaction): bool
    {
        return $this->mutate(fn() => $clientTransaction->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
