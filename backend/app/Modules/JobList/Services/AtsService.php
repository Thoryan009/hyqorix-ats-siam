<?php

namespace App\Modules\JobList\Services;

use App\Events\JobProcessSubmitted;
use App\Events\NextProcessCreated;
use App\Modules\JobList\Events\BulkNextProcessCreated;
use App\Modules\JobList\Models\JobList;
use App\Modules\JobList\Repositories\AtsRepository;
use App\Services\BaseCachedService;

class AtsService extends BaseCachedService
{
    public function __construct(protected AtsRepository $repository)
    {
        parent::__construct(new JobList());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn() => $this->repository->getPaginatedData($filters)
        );
    }

    public function getJobAtsByJobIdAndProcessId($job_id = null, $process_id = null, $application_id = null)
    {
        return $this->remember(
            $this->filtersCacheKey([
                'job_id' => $job_id,
                'process_id' => $process_id,
                'application_id' => $application_id,
            ]),
            fn() => $this->repository->getJobWithApplications($job_id, $process_id, $application_id)
        );
    }


    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */


    public function createNextProcess(array $data)
    {
        NextProcessCreated::dispatch($data);
        return true;
    }


    public function createBulkNextProcess(array $data)
    {
        BulkNextProcessCreated::dispatch($data);
        return true;
    }

    public function updateJobProcess(array $data)
    {
        JobProcessSubmitted::dispatch($data);
        return true;
    }



    protected function getJobListWorkOrdersCacheKey(): string
    {
        return $this->getCacheTag() . '_work_orders';
    }

    protected function getJobListClientsCacheKey(): string
    {
        return $this->getCacheTag() . '_clients';
    }

}
