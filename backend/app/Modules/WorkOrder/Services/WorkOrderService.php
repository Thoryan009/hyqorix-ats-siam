<?php

namespace App\Modules\WorkOrder\Services;

use App\Modules\Client\Models\Client;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Services\BaseCachedService;
use App\Modules\WorkOrder\Repositories\WorkOrderRepository;

class WorkOrderService extends BaseCachedService
{

    public function __construct(protected WorkOrderRepository $repository)
    {
        parent::__construct(new WorkOrder());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(
        array $filters = []
    ) {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getById(int $id)
    {
        return $this->remember(
            $this->byIdCacheKey($id),
            fn () => $this->model->findOrFail($id)
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function create(array $data, $client)
    {
        $data['work_order_id'] = $this->generateWorkOrderId($client);
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

    /* ==========================================================
     | Cache Helpers
     |========================================================== */

    protected function getWorkOrderClientsCacheKey(): string
    {
        return $this->getCacheTag() . '_clients';
    }

    private function generateWorkOrderId(?Client $client): string
    {
        if (!$client) {
            throw new \InvalidArgumentException('Client is required to generate work order ID');
        }

        // Get last work order for this client
        $lastWorkOrder = $this->model
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = $lastWorkOrder
            ? (int) substr($lastWorkOrder->work_order_id, strrpos($lastWorkOrder->work_order_id, '-') + 1)
            : 0;

        $newNumber = $lastNumber + 1;

        return  'DL-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function countByIdsAndClient(array $ids, int $clientId): int
    {
        return $this->model->whereIn('id', $ids)->where('client_id', $clientId)->count();
    }
}
