<?php

namespace App\Modules\JobList\Services;

use App\Modules\JobList\Models\JobListDetail;
use App\Services\BaseCachedService;
use App\Modules\JobList\Repositories\JobListDetailRepository;
use App\Modules\JobList\Models\JobListDetailsHead;

class JobListDetailService extends BaseCachedService
{
    public function __construct(protected JobListDetailRepository $repository)
    {
        parent::__construct(new JobListDetail());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }
       public function getById(int $id)
    {
        return $this->remember(
            $this->byIdCacheKey($id),
            fn() => $this->model->with(['jobList', 'jobListDetailsHead'])->findOrFail($id)
        );
    }
    public function getJobListDetailHeads()
    {
        return JobListDetailsHead::select('id', 'name', 'amount', 'amount_usd', 'job_list_details_category_id')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($heads) {
                return [
                    'id' => $heads->id,
                    'name' => $heads->name . ' ( ' . $heads->JobListDetailsCategory->name . ' )',
                    'amount' => $heads->amount,
                    'amount_usd' => $heads->amount_usd,
                    'category_id' => $heads->JobListDetailsCategory->id,
                    'category_name' => $heads->JobListDetailsCategory->name,
                ];
            });
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getJobListDetail(JobListDetail $jobListDetail)
    {
        return $this->remember(
            $this->byIdCacheKey($jobListDetail->id),
            fn () => $jobListDetail
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createJobListDetail(array $data)
    {
        $jobListDetail = $this->mutate(fn() => $this->model->create($data));
        return $jobListDetail;
    }

        public function bulkCreate(array $dataArray)
    {
        $now = now();
        $dataToInsert = collect($dataArray)->map(function ($data) use ($now) {
            return array_merge($data, [
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        })->toArray();

        $this->model->insert($dataToInsert);
        $this->flushCache();

        // Return the inserted records
        return $this->model
            ->where('created_at', $now)
            ->latest('id')
            ->limit(count($dataToInsert))
            ->get();
    }

    public function updateJobListDetail(JobListDetail $jobListDetail, array $data)
    {
        return $this->mutate(fn() => tap($jobListDetail)->update($data));
    }

    public function deleteJobListDetail(JobListDetail $jobListDetail): bool
    {
        return $this->mutate(fn() => $jobListDetail->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
