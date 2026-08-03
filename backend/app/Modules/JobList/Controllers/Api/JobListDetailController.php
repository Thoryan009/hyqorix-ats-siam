<?php

namespace App\Modules\JobList\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\JobList\Models\JobListDetail;
use App\Modules\JobList\Requests\JobListDetailRequest;
use App\Modules\JobList\Resources\JobListDetailResource;
use App\Modules\JobList\Services\JobListDetailService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobListDetailController extends Controller
{
    public function __construct(
        private readonly JobListDetailService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['job_list_id'] = $request->get('job_id', null);
        $filters['job_list_details_head_id'] = $request->get('job_list_details_head_id', null);
        $filters['with'] = ['jobListDetailsHead.jobListDetailsCategory'];
        $jobListDetailData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return JobListDetailResource::collection($jobListDetailData);
    }

    public function store(JobListDetailRequest $request): JsonResponse
    {
        $jobListDetail = $this->service->createJobListDetail($request->validated());

        return apiSuccess(
            new JobListDetailResource($jobListDetail),
            'created'
        );
    }

     public function createBulk(Request $request): JsonResponse
    {
        $records = $this->service->bulkCreate($request->all());
        return apiSuccess(
            JobListDetailResource::collection($records),
            'created'
        );
    }

    public function show(JobListDetail $jobListDetail): JobListDetailResource
    {
        return new JobListDetailResource(
            $this->service->getJobListDetail($jobListDetail)
        );
    }

    public function update(JobListDetailRequest $request, JobListDetail $jobListDetail): JsonResponse
    {
        $jobListDetail = $this->service->updateJobListDetail(
            $jobListDetail,
            $request->validated()
        );

        return apiSuccess(
            new JobListDetailResource($jobListDetail),
            'updated'
        );
    }

    public function destroy(JobListDetail $jobListDetail): JsonResponse
    {
        $this->service->deleteJobListDetail($jobListDetail);

        return apiSuccess(
            null,
            'deleted'
        );
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->service->bulkDelete($request->ids);

        return apiSuccess(
            null,
            'deleted',
            200,
            'Records'
        );
    }

    public function getJobListDetailHeads(Request $request): JsonResponse
    {
        $heads = $this->service->getJobListDetailHeads();
        return apiSuccess(
            $heads,
            'fetched',
            200,
            'Job List Detail Heads'
        );
    }
}
