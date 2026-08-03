<?php

namespace App\Modules\JobList\Services;

use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;
use App\Modules\JobList\Contracts\WorkOrderServiceInterface;

class WorkOrderDbService implements WorkOrderServiceInterface
{
    public function getWorkOrders(?int $clientId = null): array
    {
        // Dynamic cache key
        $cacheKey = $clientId
            ? "work_orders_client_{$clientId}"
            : "work_orders_all";

        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () use ($clientId) {

            $query = WorkOrder::query();

            if ($clientId) {
                $query->where('client_id', $clientId);
            }

            return $query->get()
                ->map(function ($workOrder) {
                    return [
                        'id'   => $workOrder->id,
                        'name' => $workOrder->work_order_id,
                    ];
                })
                ->toArray();
        });
    }
}
