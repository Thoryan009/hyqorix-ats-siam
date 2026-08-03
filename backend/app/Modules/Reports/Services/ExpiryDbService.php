<?php

namespace App\Modules\Reports\Services;

use App\Modules\Agent\Models\Agent;
use App\Modules\Application\Models\Application;
use App\Modules\Client\Models\Client;
use App\Modules\JobList\Models\JobList;
use Illuminate\Support\Facades\Cache;

class ExpiryDbService
{
    public function getExpiryReport()
    {
        $cacheKey = 'clients_' . auth()->id(); // ✅ FIXED
        $cacheTTL = 2592000;
        return Cache::remember($cacheKey, $cacheTTL, function () {
             $clients = Client::query()
                ->with('user:id,name') // Eager load the related User model with only 'id' and 'name' fields
                ->select('clients.id', 'clients.user_id')
                 ->addSelect([
                'applications_count' => Application::query()
                    ->selectRaw('count(*)')
                    ->join('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
                    ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
                    ->whereColumn('work_orders.client_id', 'clients.id'),
                ])

                ->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })
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

            $agents = Agent::query()
              ->select('agents.id', 'agents.user_id')
                ->with('user:id,name')
                ->join('users', 'agents.user_id', '=', 'users.id')
                ->withCount('applications')
                ->orderBy('users.name', 'asc')
                ->get()
                ->map(fn($agent) => [
                    'id' => $agent->id,
                    'applications_count' => $agent->applications_count,
                    'name' => sprintf(
                        '%s (%d)',
                        optional($agent->user)->name ?? 'N/A',
                        $agent->applications_count
                    ),
                ])
                ->toArray();

            $job = JobList::query()
                ->select('id', 'name', 'job_code')
                ->where('status', 'open')
                ->orderByDesc('id')
            ->get()
            ->map(fn ($job) => [
                'id'       => $job->id,
                'job_name' => $job->name,
                'job_code' => $job->job_code,
                'name'     => sprintf('%s (%s)', $job->name, $job->job_code),
            ])
            ->toArray();

                return [
                    'clients' => $clients,
                    'agents' => $agents,
                    'jobs' => $job,
                ];

        });
    }
}
