<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Contracts\EmbasySubmissionDataServiceInterface;
use App\Modules\Application\Models\Application;

class EmbasySubmissionDataDbService implements EmbasySubmissionDataServiceInterface
{

    public function formatKSAData(): array
    {
        $applications = Application::query()
            ->whereHas('processes', function ($query) {
                $query->whereJsonContains('data->medical_fit', 'fit');
            })
            ->with([
                'jobList.workOrder.client.user',
                'jobList.workOrder.client.country',
                'agent.user',
            ])->whereHas('jobList.workOrder.client.country', fn($q) => $q->where('id', 10))
            ->get();

        $jobLists = $applications
            ->filter(fn ($app) => $app->jobList)
            ->groupBy('job_list_id')
            ->map(function ($group) {
                $job = $group->first()->jobList;

                return [
                    'id' => $job->id,
                    'job_name' => $job->name,
                    'job_code' => $job->job_code,
                    'application_count' => $group->count(),
                    'name' => sprintf(
                        '%s (%s) - %s',
                         \Illuminate\Support\Str::limit(
                            $job->name ?? 'N/A',
                            25
                        ),
                        $job->job_code,
                        $group->count()
                    ),
                ];
            })
            ->values()
            ->sortBy('job_name')
            ->values()
            ->toArray();

        $workOrders = $applications
            ->filter(fn ($app) => $app->jobList?->workOrder)
            ->groupBy(fn ($app) => $app->jobList->workOrder->id)
            ->map(function ($group) {
                $workOrder = $group->first()->jobList->workOrder;

                return [
                    'id' => $workOrder->id,
                    'application_count' => $group->count(),
                    'name' => sprintf(
                        '%s (%s)',
                        $workOrder->work_order_id,
                        $group->count()
                    ),
                ];
            })
            ->values()
            ->sortBy('name')
            ->values()
            ->toArray();

        $clients = $applications
            ->filter(fn ($app) => $app->jobList?->workOrder?->client)
            ->groupBy(fn ($app) => $app->jobList->workOrder->client->id)
            ->map(function ($group) {
                $client = $group->first()->jobList->workOrder->client;

                return [
                    'id' => $client->id,
                    'application_count' => $group->count(),
                    'name' => sprintf(
                        '%s (%s)',
                        \Illuminate\Support\Str::limit(
                            $client->user?->name ?? 'N/A',
                            20
                        ),
                        $group->count()
                    ),
                ];
            })
            ->values()
            ->sortBy('name')
            ->values()
            ->toArray();

        $agents = $applications
            ->filter(fn ($app) => $app->agent)
            ->groupBy('agent_id')
            ->map(function ($group) {
                $agent = $group->first()->agent;

                return [
                    'id' => $agent->id,
                    'application_count' => $group->count(),
                    'name' => sprintf(
                        '%s (%s)',
                        $agent->user?->name ?? 'N/A',
                        $group->count()
                    ),
                ];
            })
            ->values()
            ->sortBy('name')
            ->values()
            ->toArray();

        return [
            'job_lists' => $jobLists,
            'work_orders' => $workOrders,
            'clients' => $clients,
            'agents' => $agents,
        ];
    }
}
