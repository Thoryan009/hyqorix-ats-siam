<?php
namespace App\Modules\JobList\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Application\Models\ApplicationProcess;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\Client\Models\Client;
use App\Modules\JobList\Contracts\AtsDataServiceInterface;
use App\Modules\JobList\Models\JobList;
use Illuminate\Support\Facades\Cache;

class AtsDataDbService implements AtsDataServiceInterface
{
    public function getAtsData($clientId = null, array $jobIds = [])
    {
        // Dynamic cache key
        $cacheKey = 'ats_data_v2_' . ($clientId ?? 'all') . '_' . (empty($jobIds) ? 'all_jobs' : implode('_', $jobIds));

        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () use ($clientId, $jobIds) {
            $query = WorkOrder::query();

            $user = auth()->user();
            $agentClientIds = [];
            if ($user && $user->type === 'agent') {
                $agentJobListIds = $user->agent?->applications()->pluck('job_list_id')->toArray() ?? [];
                $agentWorkOrderIds = JobList::whereIn('id', $agentJobListIds)->pluck('work_order_id')->toArray();
                $agentClientIds = WorkOrder::whereIn('id', $agentWorkOrderIds)->pluck('client_id')->toArray();
            }

            if ($clientId) {
                $query->where('client_id', $clientId);
            } elseif (!empty($agentClientIds)) {
                $query->whereIn('client_id', $agentClientIds);
            }

            $workOrder = $query
                ->get()
                ->map(function ($workOrder) {
                    return [
                        'id' => $workOrder->id,
                        'name' => $workOrder->work_order_id,
                    ];
                })
                ->toArray();

            $clientQuery = Client::query();

            if ($user && $user->type === 'agent') {
                $clientQuery->whereIn('id', $agentClientIds);
            }

            $clients = $clientQuery
                ->select('clients.id', 'clients.user_id')
                ->addSelect([
                    'applications_count' => Application::query()
                        ->selectRaw('count(*)')
                        ->join('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
                        ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
                        ->whereColumn('work_orders.client_id', 'clients.id')
                        ->whereRaw("UPPER(applications.application_status) = 'ATS'"),
                ])
                ->whereHas('jobLists.applications', function ($query) {
                    $query->whereRaw("UPPER(applications.application_status) = 'ATS'");
                })
                ->with('user:id,name')
                ->join('users', 'clients.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->get()
                ->map(
                    fn ($client) => [
                        'id' => $client->id,
                        'applications_count' => (int) $client->applications_count,
                        'name' => sprintf(
                            '%s (%d)',
                            optional($client->user)->name ?? 'N/A',
                            $client->applications_count
                        ),
                    ],
                )
                ->toArray();

            $jobLists = JobList::query()
                ->select('job_lists.id', 'job_lists.name', 'job_lists.job_code')
                ->addSelect([
                    'ats_applications_count' => Application::query()
                        ->selectRaw('count(*)')
                        ->whereColumn('applications.job_list_id', 'job_lists.id')
                        ->whereRaw("UPPER(applications.application_status) = 'ATS'"),
                ])
                ->where('status', 'open')
                ->when(!empty($jobIds), function ($query) use ($jobIds) {
                    $query->whereIn('job_lists.id', $jobIds);
                })
                ->orderByDesc('job_lists.id')
                ->get()
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'job_name' => $job->name,
                        'job_code' => $job->job_code,
                        'ats_applications_count' => (int) $job->ats_applications_count,
                        'name' => sprintf(
                            '%s (%s) (%d)',
                            $job->name,
                            $job->job_code,
                            $job->ats_applications_count
                        ),
                    ];
                })
                ->toArray();
            // Job Lists

            return [
                'work_orders' => $workOrder,
                'clients' => $clients,
                'job_lists' => $jobLists,
            ];
        });
    }

    public function flushCache()
    {
        Cache::forget('ats_data_all');
    }

    public function deleteCurrentApplicationProcess($applicationProcessId)
    {
        ApplicationProcess::findOrFail($applicationProcessId)->delete();

        $this->flushCache();
    }
}
