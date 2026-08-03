<?php

namespace App\Modules\Reports\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Reports\Contracts\ClientServiceInterface;
use App\Modules\Client\Models\Client;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;

class ClientDbService implements ClientServiceInterface
{
    public $cacheKey = 'client_data_all';
   public function getClients(): array
{
    if (auth()->user()->type === 'client') {
        return [];
    }

    $cacheTTL = 2592000; // 30 days

    $agent = auth()->user()->agent;
    $principal = auth()->user()->principal; // ✅ NEW

    $agentClientIds = [];
    $principalClientIds = []; // ✅ NEW

    // ✅ Agent Logic (UNCHANGED)
    if ($agent) {
        $agentClientIds = WorkOrder::query()
            ->join('job_lists', 'work_orders.id', '=', 'job_lists.work_order_id')
            ->join('applications', 'job_lists.id', '=', 'applications.job_list_id')
            ->where('applications.agent_id', $agent->id)
            ->pluck('work_orders.client_id')
            ->unique()
            ->toArray();
    }

    // ✅ Principal Logic (NEW)
    if ($principal) {
        $principalClientIds = WorkOrder::query()
            ->join('job_lists', 'work_orders.id', '=', 'job_lists.work_order_id')
            ->where('job_lists.principal_id', $principal->id)
            ->pluck('work_orders.client_id')
            ->unique()
            ->toArray();
    }

    // ✅ Merge both
    $mergeClientIds = array_merge($agentClientIds, $principalClientIds);
    $mergeClientIds = array_unique($mergeClientIds);

    return Cache::remember($this->cacheKey, $cacheTTL, function () use ($mergeClientIds) {
        return Client::query()
            ->select('clients.id', 'clients.user_id')
            ->addSelect([
                'applications_count' => Application::query()
                    ->selectRaw('count(*)')
                    ->join('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
                    ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
                    ->whereColumn('work_orders.client_id', 'clients.id'),
            ])
            ->when(!empty($mergeClientIds), function ($query) use ($mergeClientIds) {
                $query->whereIn('clients.id', $mergeClientIds);
            })
            ->with('user:id,name')
            ->join('users', 'clients.user_id', '=', 'users.id')
            ->orderBy('users.name', 'asc')
            ->get()
            ->map(fn($client) => [
                'id' => $client->id,
                'applications_count' => (int) $client->applications_count,
                'name' => sprintf(
                    '%s (%d)',
                    optional($client->user)->name ?? 'N/A',
                    $client->applications_count
                ),
            ])
            ->toArray();
    });
}
    public function clearClientDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
