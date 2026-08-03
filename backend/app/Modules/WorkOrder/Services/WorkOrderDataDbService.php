<?php

namespace App\Modules\WorkOrder\Services;

use App\Modules\Client\Models\Client;
use App\Modules\Employee\Models\Employee;
use Illuminate\Support\Facades\Cache;

use App\Modules\WorkOrder\Contracts\WorkOrderDataServiceInterface;

class WorkOrderDataDbService implements WorkOrderDataServiceInterface
{
    public function getWorkOrderData(): array
    {
        $cacheKey = 'work_order_data_all_v2';
        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () {

            // For admin or other users, show all clients and recruiters

            $user = auth()->user();
            $clientId = null;
            if ($user->type === 'client') {
                $clientId = $user->client->id;
            }

            $clients = Client::query()->with('user')->when($clientId, fn($query) => $query->where('id', $clientId))->get()->map(fn($client) => [
                'id' => $client->id,
                'name' => $client->user?->name ?? 'N/A',
            ])->toArray();

            $employees = Employee::query()
                ->with('user')
                ->join('users', 'employees.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('employees.*')
                ->get()
                ->map(fn($employee) => [
                    'id' => $employee->id,
                    'name' => $employee->user?->name ?? 'N/A',
                ])
                ->toArray();

            $filterEmployees = Employee::query()
                ->with('user')
                ->whereHas('workOrders')
                ->join('users', 'employees.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('employees.*')
                ->withCount('workOrders')
                ->get()
                ->map(fn($employee) => [
                    'id' => $employee->id,
                    'name' => $employee->user?->name ?? 'N/A',
                    'work_orders_count' => (int) $employee->work_orders_count,
                ])
                ->toArray();

            return [
                'clients' => $clients,
                'employees' => $employees,
                'filter_employees' => $filterEmployees,
            ];
        });
    }
    public function clearWorkOrderDataCache(): void
    {
        Cache::forget('work_order_data_all_v2');
        Cache::forget('work_order_data_all');
    }
}
