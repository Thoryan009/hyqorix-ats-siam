<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Contracts\WorkOrderServiceInterface;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;


class WorkOrderDbService implements WorkOrderServiceInterface
{

    public function getWorkOrderById(int $id): ?WorkOrder
    {
        $cacheKey = "client_bill_work_order_{$id}";
        $cacheTTL = 3600; // 1 hour in seconds

        return Cache::remember($cacheKey, $cacheTTL, function () use ($id) {
            $workOrder = WorkOrder::with(['client.user'])->find($id);

            if (!$workOrder) {
                return null;
            }

           return $workOrder;
        });
    }
}
