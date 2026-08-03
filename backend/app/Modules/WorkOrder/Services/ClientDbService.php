<?php

namespace App\Modules\WorkOrder\Services;

use App\Modules\WorkOrder\Contracts\ClientServiceInterface;
use App\Modules\Client\Models\Client;
use Illuminate\Support\Facades\Cache;

class ClientDbService implements ClientServiceInterface
{
    public function getClients(): array
    {
        $cacheKey = 'workorder_filter_clients_v2';
        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $user = auth()->user();

            $query = Client::query()
                ->with('user')
                ->whereHas('workOrders')
                ->join('users', 'clients.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('clients.*')
                ->withCount('workOrders');

            if ($user->type === 'client') {
                $query->where('clients.id', $user->client->id);
            }

            return $query->get()->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->user?->name ?? 'N/A',
                    'work_orders_count' => (int) $client->work_orders_count,
                ];
            })->toArray();
        });
    }

    public function getClientById(int $id)
    {
        $cacheKey = "client_{$id}";
        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () use ($id) {
            $client = Client::find($id); // fetch from DB

            if (!$client) {
                return null; // or throw an exception
            }

            return $client;
        });
    }
}
