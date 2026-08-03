<?php

namespace App\Modules\Reports\Services;

use App\Modules\JobList\Models\JobList;
use App\Modules\Reports\Contracts\WorkOrderServiceInterface;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;

class WorkOrderDbService implements WorkOrderServiceInterface
{
    public $cacheKey = 'report_work_orders';
 
    public function getWorkOrders(): array
{
    $cacheTTL = 2592000; // 30 days

    $agent = auth()->user()->agent;
    $client = auth()->user()->client;
    $principal = auth()->user()->principal; // ✅ NEW

    $agentWorkOrderIds = [];
    $clientWorkOrderIds = [];
    $principalWorkOrderIds = []; // ✅ NEW

    // ✅ Agent Logic (UNCHANGED)
    if ($agent) {
        $agentWorkOrderIds = JobList::query()
            ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
            ->join('applications', 'job_lists.id', '=', 'applications.job_list_id')
            ->where('applications.agent_id', $agent->id)
            ->pluck('work_orders.id')
            ->unique()
            ->toArray();
    }

    // ✅ Client Logic (UNCHANGED)
    if ($client) {
        $clientWorkOrderIds = WorkOrder::query()
            ->where('work_orders.client_id', $client->id)
            ->pluck('work_orders.id')
            ->unique()
            ->toArray();
    }

    // ✅ Principal Logic (NEW - EXACTLY like you asked)
    if ($principal) {
        $principalWorkOrderIds = JobList::query()
            ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
            ->where('job_lists.principal_id', $principal->id)
            ->pluck('work_orders.id')
            ->unique()
            ->toArray();
    }

    // ✅ Merge all (agent + client + principal)
    $mergeWorkOrderIds = array_merge(
        $agentWorkOrderIds,
        $clientWorkOrderIds,
        $principalWorkOrderIds // ✅ added
    );

    $mergeWorkOrderIds = array_unique($mergeWorkOrderIds);

    return Cache::remember($this->cacheKey, $cacheTTL, function () use ($mergeWorkOrderIds) {
        return WorkOrder::query()
            ->select('id', 'work_order_id', 'client_id')
            ->with('client.user')
            ->when(!empty($mergeWorkOrderIds), function ($query) use ($mergeWorkOrderIds) {
                $query->whereIn('id', $mergeWorkOrderIds);
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn($workOrder) => [
                'id'   => $workOrder->id,
                'name' => sprintf(
                    '%s (%s)',
                    $workOrder->work_order_id ?? 'N/A',
                    $workOrder->client?->user?->name ?? 'N/A'
                ),
            ])
            ->toArray();
    });
}

    public function clearWorkOrderCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
