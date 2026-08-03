<?php

namespace App\Modules\Application\Services;

use App\Modules\Agent\Models\Agent;
use App\Modules\JobList\Models\JobList;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\Client\Models\Client;
use Illuminate\Support\Facades\Cache;
use App\Modules\Application\Contracts\ApplicationDataServiceInterface;
use App\Modules\Application\Models\Application;
use App\Modules\Application\Models\Qualification;
use App\Modules\Application\Models\Subject;
use App\Modules\Application\Repositories\ProcessRepository;
use App\Modules\Application\Services\ProcessService;
use Illuminate\Support\Str;

class ApplicationDataDbService implements ApplicationDataServiceInterface
{
    private $cacheKey = 'application_data_all_v6';

    public function __construct(
        private readonly ProcessRepository $processRepository,
        private readonly ProcessService $processService,
    ) {
    }

    public function getApplicationData(): array
    {
        return Cache::remember($this->cacheKey, 3600, function () {
            $user = auth()->user();

            if ($user?->type === 'client') {
                return $this->getClientData($user->client?->id);
            }

            if ($user?->type === 'agent') {
                return $this->getAgentData($user->agent?->id);
            }
            return $this->getAdminData();
        });
    }

    /**
     * ================= CLIENT DATA =================
     */
    private function getClientData(?int $clientId): array
    {
        if (!$clientId)
            return [];

        return [
            'job_lists' => $this->formatJobLists(
                JobList::whereHas('workOrder', fn($q) => $q->where('client_id', $clientId))
            ),
            'filter_job_lists' => $this->formatFilterJobLists(
                JobList::whereHas('workOrder', fn($q) => $q->where('client_id', $clientId))
            ),
            'filter_processes' => $this->processRepository->getFilterProcessList(
                fn ($q) => $q->whereHas('jobList.workOrder', fn ($q2) => $q2->where('client_id', $clientId)),
                $this->processService->getHiringListProcessId()
            ),
            'filter_work_orders' => $this->formatFilterWorkOrders(
                WorkOrder::where('client_id', $clientId)->orderByDesc('id')
            ),
            'filter_statuses' => $this->formatFilterStatuses(
                fn ($q) => $q->whereHas('jobList.workOrder', fn ($q2) => $q2->where('client_id', $clientId))
            ),

            'work_orders' => $this->formatWorkOrders(
                WorkOrder::where('client_id', $clientId)->with('client.user')
            ),

            'clients' => $this->formatClients(
                Client::where('id', $clientId)->with('user')
            ),
        ];
    }

    /**
     * ================= AGENT DATA =================
     */
    private function getAgentData(?int $agentId): array
    {
        if (!$agentId)
            return [];

        return [
            'job_lists' => $this->formatJobLists(
                JobList::whereIn('id', function ($q) use ($agentId) {
                    $q->select('job_list_id')
                        ->from('applications')
                        ->where('agent_id', $agentId);
                })
            ),
            'filter_job_lists' => $this->formatFilterJobLists(
                JobList::whereIn('id', function ($q) use ($agentId) {
                    $q->select('job_list_id')
                        ->from('applications')
                        ->where('agent_id', $agentId);
                })
            ),
            'filter_processes' => $this->processRepository->getFilterProcessList(
                fn ($q) => $q->where('agent_id', $agentId),
                $this->processService->getHiringListProcessId()
            ),
            'filter_clients' => $this->formatFilterClients(
                Client::query()
                    ->with('user')
                    ->whereHas('jobLists.applications', fn ($q) => $q->where('agent_id', $agentId))
                    ->join('users', 'clients.user_id', '=', 'users.id')
                    ->orderBy('users.name', 'asc')
                    ->select('clients.*'),
                fn ($q) => $q->where('agent_id', $agentId)
            ),
            'filter_statuses' => $this->formatFilterStatuses(
                fn ($q) => $q->where('agent_id', $agentId)
            ),

            'clients' => $this->formatClients(
                Client::whereHas('workOrders.jobLists.applications', function ($q) use ($agentId) {
                    $q->where('agent_id', $agentId);
                })->with('user')
            ),
            'agents' => $this->formatAgents(
                Agent::where('id', $agentId)->with('user')
            ),

        ];
    }

    /**
     * ================= ADMIN / DEFAULT =================
     */
    private function getAdminData(): array
    {
        return [
            'job_lists' => $this->formatJobLists(JobList::query()),
            'filter_job_lists' => $this->formatFilterJobLists(JobList::query()),
            'filter_processes' => $this->processRepository->getFilterProcessList(
                fn ($q) => $q,
                $this->processService->getHiringListProcessId()
            ),
            'filter_work_orders' => $this->formatFilterWorkOrders(
                WorkOrder::query()->orderByDesc('id')
            ),
            'filter_clients' => $this->formatFilterClients(
                Client::query()
                    ->with('user')
                    ->join('users', 'clients.user_id', '=', 'users.id')
                    ->orderBy('users.name', 'asc')
                    ->select('clients.*')
            ),
            'filter_agents' => $this->formatFilterAgents(
                Agent::query()
                    ->with('user')
                    ->join('users', 'agents.user_id', '=', 'users.id')
                    ->orderBy('users.name', 'asc')
                    ->select('agents.*')
            ),
            'filter_statuses' => $this->formatFilterStatuses(fn ($q) => $q),

            'work_orders' => $this->formatWorkOrders(
                WorkOrder::query()->with('client.user')->orderByDesc('id')
            ),

            'clients' => $this->formatClients(
                Client::query()
                    ->with('user')
                    ->join('users', 'clients.user_id', '=', 'users.id')
                    ->orderBy('users.name', 'asc')
                    ->select('clients.*')
            ),

            'agents' => $this->formatAgents(
                Agent::query()
                    ->with('user')
                    ->join('users', 'agents.user_id', '=', 'users.id')
                    ->orderBy('users.name', 'asc')
                    ->select('agents.*')
            ),

            'subjects' => $this->formatSimple(Subject::query()->orderBy('name', 'asc')),
            'qualifications' => $this->formatSimple(Qualification::query()),
        ];
    }

    /**
     * ================= FORMATTERS =================
     */
    private function formatJobLists($query): array
    {
        return $query
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($job) => [
                'id' => $job->id,
                'job_name' => $job->name,
                'job_code' => $job->job_code,
                'name' => sprintf('%s (%s)', $job->name, $job->job_code),
            ])
            ->toArray();
    }

    private function formatFilterJobLists($query): array
    {
        return $query
            ->whereHas('applications')
            ->withCount('applications')
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($job) => [
                'id' => $job->id,
                'job_name' => $job->name,
                'job_code' => $job->job_code,
                'applications_count' => (int) $job->applications_count,
                'name' => sprintf('%s (%s) (%d)', $job->name, $job->job_code, $job->applications_count),
            ])
            ->toArray();
    }

    private function formatWorkOrders($query): array
    {
        return $query->get()->map(fn($wo) => [
            'id' => $wo->id,
            'name' => sprintf(
                '%s (%s)',
                $wo->work_order_id ?? 'N/A',
                $wo->client?->user?->name ?? 'N/A'
            ),
        ])->toArray();
    }

    private function formatFilterWorkOrders($query): array
    {
        return $query
            ->whereHas('applications')
            ->with(['client.user'])
            ->withCount('applications')
            ->get()
            ->map(function ($workOrder) {
                $clientName = $workOrder->client?->user?->name ?? 'N/A';
                $workOrderId = $workOrder->work_order_id ?? 'N/A';

                return [
                    'id' => $workOrder->id,
                    'work_order_id' => $workOrderId,
                    'client_name' => $clientName,
                    'applications_count' => (int) $workOrder->applications_count,
                    'search_name' => sprintf('%s %s', $workOrderId, $clientName),
                    'name' => sprintf(
                        '%s (%s) (%d)',
                        $workOrderId,
                        $this->truncateString($clientName, 18),
                        $workOrder->applications_count
                    ),
                ];
            })
            ->toArray();
    }

    private function formatFilterClients($query, ?callable $applicationScope = null): array
    {
        $clients = $query
            ->whereHas('jobLists.applications', $applicationScope ?? fn ($q) => $q)
            ->get();

        if ($clients->isEmpty()) {
            return [];
        }

        $countQuery = Application::query()
            ->join('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
            ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
            ->whereIn('work_orders.client_id', $clients->pluck('id'));

        if ($applicationScope) {
            $applicationScope($countQuery);
        }

        $counts = $countQuery
            ->groupBy('work_orders.client_id')
            ->selectRaw('work_orders.client_id, count(*) as applications_count')
            ->pluck('applications_count', 'client_id');

        return $clients
            ->map(function ($client) use ($counts) {
                $count = (int) ($counts[$client->id] ?? 0);

                if ($count === 0) {
                    return null;
                }

                $fullName = $client->user?->name ?? 'N/A';

                return [
                    'id' => $client->id,
                    'search_name' => $fullName,
                    'applications_count' => $count,
                    'name' => sprintf('%s (%d)', $this->truncateString($fullName, 18), $count),
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function formatFilterAgents($query): array
    {
        return $query
            ->whereHas('applications')
            ->withCount('applications')
            ->get()
            ->map(function ($agent) {
                $fullName = $agent->user?->name ?? 'N/A';

                return [
                    'id' => $agent->id,
                    'search_name' => $fullName,
                    'applications_count' => (int) $agent->applications_count,
                    'name' => sprintf('%s (%d)', $this->truncateString($fullName, 18), $agent->applications_count),
                ];
            })
            ->toArray();
    }

    private function formatFilterStatuses(callable $scopeApplications): array
    {
        $baseQuery = fn () => tap(Application::query(), $scopeApplications);

        $statuses = [
            'hiring_list' => 'Hiring List',
            'waiting_list' => 'Waiting List',
            'rejected_list' => 'Rejected List',
            'application_list' => 'Application List',
            'short_list' => 'Short List',
        ];

        $items = [];

        foreach ($statuses as $key => $label) {
            $count = $baseQuery()->whereRaw('LOWER(application_status) = ?', [strtolower($key)])->count();

            if ($count > 0) {
                $items[] = [
                    'id' => $key,
                    'name' => sprintf('%s (%d)', $label, $count),
                ];
            }
        }

        return $items;
    }

    private function truncateString(?string $value, int $maxLength): string
    {
        return Str::limit($value ?: 'N/A', $maxLength, '...');
    }

    private function formatClients($query): array
    {
        return $query->get()->map(fn($client) => [
            'id' => $client->id,
            'name' => $client->user?->name ?? 'N/A',
        ])->toArray();
    }

    private function formatAgents($query): array
    {
        return $query->get()->map(fn($agent) => [
            'id' => $agent->id,
            'name' => $agent->user?->name ?? 'N/A',
        ])->toArray();
    }

    private function formatSimple($query): array
    {
        return $query->get()->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
        ])->toArray();
    }

    public function formatJoblistForHiringList(): array
    {
        return $this->formatJoblistForApplicationStatus('hiring_list');
    }

    public function formatJoblistForApplicationList(): array
    {
        return $this->formatJoblistForApplicationStatus('application_list');
    }

    public function formatJoblistForShortList(): array
    {
        return $this->formatJoblistForApplicationStatus('short_list');
    }

    public function formatJoblistForWaitingList(): array
    {
        return $this->formatJoblistForApplicationStatus('waiting_list');
    }

    public function formatJoblistForRejectedList(): array
    {
        return $this->formatJoblistForApplicationStatus('rejected_list');
    }

    private function formatJoblistForApplicationStatus(string $status): array
    {
        $countColumn = "{$status}_count";

        return JobList::query()
            ->whereHas('applications', function ($query) use ($status) {
                $query->where('application_status', $status);
            })
            ->withCount([
                "applications as {$countColumn}" => function ($query) use ($status) {
                    $query->where('application_status', $status);
                },
            ])
            ->orderBy('name')
            ->get()
            ->map(fn ($job) => [
                'id' => $job->id,
                'job_name' => $job->name,
                'job_code' => $job->job_code,
                'application_count' => $job->{$countColumn},
                'name' => sprintf(
                    '%s (%s) - %s ',
                    $job->name,
                    $job->job_code,
                    $job->{$countColumn}
                ),
            ])
            ->toArray();
    }

    public function formatClientsForHiringList(): array
    {
        return $this->formatClientsForApplicationStatus('hiring_list');
    }

    public function formatClientsForApplicationList(): array
    {
        return $this->formatClientsForApplicationStatus('application_list');
    }

    public function formatClientsForShortList(): array
    {
        return $this->formatClientsForApplicationStatus('short_list');
    }

    public function formatClientsForWaitingList(): array
    {
        return $this->formatClientsForApplicationStatus('waiting_list');
    }

    public function formatClientsForRejectedList(): array
    {
        return $this->formatClientsForApplicationStatus('rejected_list');
    }

    private function formatClientsForApplicationStatus(string $status): array
    {
        return Client::query()
            ->whereHas('workOrders.jobLists.applications', function ($query) use ($status) {
                $query->where('application_status', $status);
            })
            ->with([
                'user',
                'workOrders.jobLists.applications' => function ($query) use ($status) {
                    $query->where('application_status', $status);
                },
            ])
            ->orderBy('client_id')
            ->get()
            ->map(function ($client) {

                $applicationCount = $client->workOrders
                    ->flatMap->jobLists
                    ->flatMap->applications
                    ->count();

                return [
                    'id' => $client->id,
                    'client_name' => $client->user?->name,
                    'application_count' => $applicationCount,
                    'name' => sprintf(
                        '%s - %s',
                        $client->user?->name,
                        $applicationCount
                    ),
                ];
            })
            ->toArray();
    }

    public function formatAgentsForHiringList(): array
    {
        return $this->formatAgentsForApplicationStatus('hiring_list');
    }

    public function formatAgentsForApplicationList(): array
    {
        return $this->formatAgentsForApplicationStatus('application_list');
    }

    public function formatAgentsForShortList(): array
    {
        return $this->formatAgentsForApplicationStatus('short_list');
    }

    public function formatAgentsForWaitingList(): array
    {
        return $this->formatAgentsForApplicationStatus('waiting_list');
    }

    public function formatAgentsForRejectedList(): array
    {
        return $this->formatAgentsForApplicationStatus('rejected_list');
    }

    private function formatAgentsForApplicationStatus(string $status): array
    {
        $countColumn = "{$status}_count";

        return Agent::query()
            ->whereHas('applications', function ($query) use ($status) {
                $query->where('application_status', $status);
            })
            ->withCount([
                "applications as {$countColumn}" => function ($query) use ($status) {
                    $query->where('application_status', $status);
                },
            ])
            ->with('user')
            ->orderBy('id')
            ->get()
            ->map(fn ($agent) => [
                'id' => $agent->id,
                'agent_name' => $agent->user?->name,
                'application_count' => $agent->{$countColumn},
                'name' => sprintf(
                    '%s - %s',
                    $agent->user?->name,
                    $agent->{$countColumn}
                ),
            ])
            ->toArray();
    }

    public function clearApplicationDataCache(): void
    {
        Cache::forget('application_data_all');
        Cache::forget($this->cacheKey);
    }
}
