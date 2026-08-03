<?php

namespace App\Modules\JobList\Services;

use App\Modules\Client\Models\Client;
use App\Modules\JobList\Contracts\JobListDataServiceInterface;
use App\Modules\JobList\Models\JobList;
use App\Modules\Principal\Models\Principal;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobListDataDbService implements JobListDataServiceInterface
{
    public function getJobListData(?int $clientId = null)
    {
        $cacheKey = $clientId
            ? "job_list_data_client_{$clientId}_v2"
            : "job_list_data_all_v2";

        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () use ($clientId) {

            $user = auth()->user();

            Log::info('JobListDataDbService START', [
                'clientId_param' => $clientId,
                'auth_user_id' => $user?->id,
                'user_type' => $user?->type,
            ]);

            // =========================
            // WORK ORDERS
            // =========================
            $workOrdersQuery = WorkOrder::query()->with('client.user');

            if ($clientId) {
                $workOrdersQuery->where('client_id', $clientId);
            }

            if ($user?->type === 'client') {
                $workOrdersQuery->where('client_id', $user->client?->id);
            }

            $workOrders = $workOrdersQuery->get()->map(function ($workOrder) {
                return [
                    'id' => $workOrder->id,
                    'name' => sprintf(
                        '%s (%s)',
                        $workOrder->work_order_id ?? 'N/A',
                        $workOrder->client?->user?->name ?? 'N/A'
                    ),
                ];
            })->toArray();

            Log::info('WorkOrders Loaded', [
                'count' => count($workOrders),
            ]);

            // =========================
            // AGENT RELATED DATA
            // =========================
            $agentApplications = $user?->agent?->applications;

            Log::info('Agent Applications', [
                'is_null' => is_null($agentApplications),
                'count' => is_iterable($agentApplications) ? count($agentApplications) : 0,
            ]);

            // SAFE COLLECTION (fix for null error)
            $agentJobIds = collect($agentApplications)
                ->pluck('job_list_id')
                ->filter()
                ->toArray();

            Log::info('Agent Job IDs', $agentJobIds);

            $agentWorkOrderIds = JobList::query()
                ->whereIn('id', $agentJobIds)
                ->pluck('work_order_id')
                ->toArray();

            Log::info('Agent WorkOrder IDs', $agentWorkOrderIds);

            $agentClientIds = WorkOrder::query()
                ->whereIn('id', $agentWorkOrderIds)
                ->pluck('client_id')
                ->toArray();

            Log::info('Agent Client IDs', $agentClientIds);

            // =========================
            // CLIENTS
            // =========================
            $authClientId = $user?->client?->id;

            $clientQuery = Client::query()->with('user');

            if ($authClientId) {
                $clientQuery->where('id', $authClientId);
            } elseif ($user?->type === 'agent') {
                $clientQuery->whereIn('id', $agentClientIds);
            } elseif ($clientId) {
                $clientQuery->where('id', $clientId);
            }

            $clients = $clientQuery->get()->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->user?->name ?? 'N/A',
                ];
            })->toArray();

            Log::info('Clients Loaded', [
                'count' => count($clients),
            ]);

            $principalQuery = Principal::query()->with('user');
              $principals = $principalQuery->get()->map(function ($principal) {
                return [
                    'id' => $principal->id,
                    'name' => $principal->user?->name ?? 'N/A',
                ];
            })->toArray();

            // =========================
            // FILTER DATA (only entities with jobs)
            // =========================
            $filterWorkOrdersQuery = WorkOrder::query()
                ->with('client.user')
                ->whereHas('jobLists')
                ->join('clients', 'work_orders.client_id', '=', 'clients.id')
                ->join('users', 'clients.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('work_orders.*')
                ->withCount('jobLists');

            if ($clientId) {
                $filterWorkOrdersQuery->where('work_orders.client_id', $clientId);
            }

            if ($authClientId) {
                $filterWorkOrdersQuery->where('work_orders.client_id', $authClientId);
            } elseif ($user?->type === 'agent') {
                $filterWorkOrdersQuery->whereIn('work_orders.id', $agentWorkOrderIds);
            }

            $filterWorkOrders = $filterWorkOrdersQuery->get()->map(function ($workOrder) {
                return [
                    'id' => $workOrder->id,
                    'work_order_id' => $workOrder->work_order_id ?? 'N/A',
                    'client_name' => $workOrder->client?->user?->name ?? 'N/A',
                    'job_lists_count' => (int) $workOrder->job_lists_count,
                ];
            })->toArray();

            $filterClientsQuery = Client::query()
                ->with('user')
                ->whereHas('jobLists')
                ->join('users', 'clients.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('clients.*')
                ->withCount('jobLists');

            if ($authClientId) {
                $filterClientsQuery->where('clients.id', $authClientId);
            } elseif ($user?->type === 'agent') {
                $filterClientsQuery->whereIn('clients.id', $agentClientIds);
            } elseif ($clientId) {
                $filterClientsQuery->where('clients.id', $clientId);
            }

            $filterClients = $filterClientsQuery->get()->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->user?->name ?? 'N/A',
                    'job_lists_count' => (int) $client->job_lists_count,
                ];
            })->toArray();

            $filterPrincipals = Principal::query()
                ->with('user')
                ->whereHas('jobLists')
                ->join('users', 'principals.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('principals.*')
                ->withCount('jobLists')
                ->get()
                ->map(function ($principal) {
                    return [
                        'id' => $principal->id,
                        'name' => $principal->user?->name ?? 'N/A',
                        'job_lists_count' => (int) $principal->job_lists_count,
                    ];
                })
                ->toArray();

            // =========================
            // FINAL OUTPUT
            // =========================
            $result = [
                'work_orders' => $workOrders,
                'clients'     => $clients,
                'principals'  => $principals,
                'filter_work_orders' => $filterWorkOrders,
                'filter_clients' => $filterClients,
                'filter_principals' => $filterPrincipals,
            ];

            Log::info('JobListDataDbService END', $result);

            return $result;
        });
    }

    public function clearJobListDataCache(): void
    {
        Cache::forget('job_list_data_all');
        Cache::forget('job_list_data_all_v2');

        Log::info('JobListData cache cleared');
    }
}
