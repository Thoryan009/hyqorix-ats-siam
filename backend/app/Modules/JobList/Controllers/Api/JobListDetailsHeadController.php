<?php

namespace App\Modules\JobList\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\JobList\Models\JobListDetailsHead;
use App\Modules\JobList\Requests\JobListDetailsHeadRequest;
use App\Modules\JobList\Resources\JobListDetailsHeadResource;
use App\Modules\JobList\Services\JobListDetailsHeadService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobListDetailsHeadController extends Controller
{
    public function __construct(
        private readonly JobListDetailsHeadService $service
    ) {
    }

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['category_id'] = $request->get('category_id', null);

        $jobListDetailsHeadData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return JobListDetailsHeadResource::collection($jobListDetailsHeadData);
    }

    public function store(JobListDetailsHeadRequest $request): JsonResponse
    {
        $jobListDetailsHead = $this->service->createJobListDetailsHead($request->validated());

        return apiSuccess(
            new JobListDetailsHeadResource($jobListDetailsHead),
            'created'
        );
    }

    public function show(JobListDetailsHead $jobListDetailsHead): JobListDetailsHeadResource
    {
        return new JobListDetailsHeadResource(
            $this->service->getJobListDetailsHead($jobListDetailsHead)
        );
    }

    public function update(JobListDetailsHeadRequest $request, JobListDetailsHead $jobListDetailsHead): JsonResponse
    {
        $jobListDetailsHead = $this->service->updateJobListDetailsHead(
            $jobListDetailsHead,
            $request->validated()
        );

        return apiSuccess(
            new JobListDetailsHeadResource($jobListDetailsHead),
            'updated'
        );
    }

    public function destroy(JobListDetailsHead $jobListDetailsHead): JsonResponse
    {
        $this->service->deleteJobListDetailsHead($jobListDetailsHead);

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
    public function getJobListHeadCategories(Request $request): JsonResponse
    {
        $category = $this->service->getJobListHeadCategories();
        return apiSuccess(
            $category,
            'fetched',
            200,
            'Job List Head Categories'
        );
    }
}
