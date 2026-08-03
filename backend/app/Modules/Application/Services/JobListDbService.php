<?php

namespace App\Modules\Application\Services;

use Illuminate\Support\Facades\Cache;
use App\Modules\Application\Contracts\JobListServiceInterface;
use App\Modules\JobList\Models\JobList;

class JobListDbService implements JobListServiceInterface
{
     public function getJobById(int $jobId): ?JobList
    {
        // Call JobList model here (monolith version)
        $job = JobList::findOrFail($jobId);
        return $job;
    }

    public function getTotalAmountByJobId(int $jobId, string $payer): float
    {
        $job = JobList::findOrFail($jobId);
        return $payer == 'candidate' ? $job->price : $job->client_commission_per_candidate;
    }


    public function getCandidateBillJobLists(): array
    {
        $cacheKey = 'candidate_bill_job_list_all';
        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () {
            return JobList::query()
                ->with(['workOrder.client.user'])
                ->where('status', 'open')
                ->get()
                ->map(fn($job) => [
                    'id'   => $job->id,
                    'name' => $job->name . ' (' . $job->job_code . ')',
                ])
                ->toArray();
        });
    }

    public function clearJobListCache(): void
    {
        Cache::tags('job_lists')->flush();

    }


}
