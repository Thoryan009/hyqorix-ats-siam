<?php

namespace App\Modules\JobList\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Client\Models\Client;
// use Illuminate\Support\Collection;
use App\Modules\JobList\Contracts\ClientServiceInterface;
use Illuminate\Support\Facades\Cache;

class ClientDbService implements ClientServiceInterface
{
    protected string $cacheTag = 'clients';
    protected int $cacheTtl = 3600; // 1 hour
    protected string $cacheKey = 'ats_clients';

    public function getAtsClients(): array
    {
        return Cache::tags($this->cacheTag)->remember(
            $this->cacheKey,
            $this->cacheTtl,
            fn() => $this->fetchAtsClients()
        );
    }

    /**
     * Pure DB query
     */
    protected function fetchAtsClients(): array
    {
        return Client::query()
            ->select('clients.id', 'clients.user_id')
            ->addSelect([
                'applications_count' => Application::query()
                    ->selectRaw('count(*)')
                    ->join('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
                    ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
                    ->whereColumn('work_orders.client_id', 'clients.id')
                    ->whereRaw("UPPER(applications.application_status) = 'ATS'"),
            ])
            ->whereHas('workOrders')
            ->with('user:id,name')
            ->join('users', 'clients.user_id', '=', 'users.id')
            ->orderBy('users.name', 'asc')
            ->get()
            ->map(fn ($client) => [
                'id' => $client->id,
                'applications_count' => (int) $client->applications_count,
                'name' => sprintf(
                    '%s (%d)',
                    optional($client->user)->name ?? 'N/A',
                    $client->applications_count
                ),
            ])
            ->toArray();
    }

   
}
