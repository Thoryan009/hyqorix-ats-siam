<?php
namespace App\Modules\Reports\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Reports\Contracts\JobServiceInterface;
use App\Modules\JobList\Models\JobList;
use Illuminate\Support\Facades\Cache;

class JobListDbService implements JobServiceInterface
{
    public $cacheKey = 'job_list';

    public function getJobs(): array
{
    $cacheTTL = 2592000; // 30 days

    $agent = auth()->user()->agent;
    $client = auth()->user()->client;
    $principal = auth()->user()->principal; // ✅ NEW

    $agentJobIds = [];
    $clientJobIds = [];
    $principalJobIds = []; // ✅ NEW

    // ✅ Agent Logic (UNCHANGED)
    if ($agent) {
        $agentJobIds = Application::query()
            ->join('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
            ->where('applications.agent_id', $agent->id)
            ->pluck('job_lists.id')
            ->unique()
            ->toArray();
    }

    // ✅ Client Logic (UNCHANGED)
    if ($client) {
        $clientJobIds = JobList::query()
            ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
            ->where('work_orders.client_id', $client->id)
            ->pluck('job_lists.id')
            ->unique()
            ->toArray();
    }

    // ✅ Principal Logic (NEW)
    if ($principal) {
        $principalJobIds = JobList::query()
            ->where('job_lists.principal_id', $principal->id)
            ->pluck('job_lists.id')
            ->unique()
            ->toArray();
    }

    // ✅ Merge all
    $mergeJobIds = array_merge(
        $agentJobIds,
        $clientJobIds,
        $principalJobIds // ✅ added
    );

    $mergeJobIds = array_unique($mergeJobIds);

    return Cache::remember($this->cacheKey, $cacheTTL, function () use ($mergeJobIds) {
        return JobList::select('id', 'name', 'job_code')
            ->where('status', 'open')
            ->when(!empty($mergeJobIds), function ($query) use ($mergeJobIds) {
                $query->whereIn('id', $mergeJobIds);
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn ($job) => [
                'id'       => $job->id,
                'job_name' => $job->name,
                'job_code' => $job->job_code,
                'name'     => sprintf('%s (%s)', $job->name, $job->job_code),
            ])
            ->toArray();
    });
}

    public function clearJobListCache(): void
    {
        Cache::forget($this->cacheKey);
    }

}
