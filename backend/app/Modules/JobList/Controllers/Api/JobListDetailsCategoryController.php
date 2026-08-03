<?php

namespace App\Modules\JobList\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\JobList\Models\JobListDetailsCategory;
use App\Modules\JobList\Requests\JobListDetailsCategoryRequest;
use App\Modules\JobList\Resources\JobListDetailsCategoryResource;
use App\Modules\JobList\Services\JobListDetailsCategoryService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobListDetailsCategoryController extends Controller
{
    public function __construct(
        private readonly JobListDetailsCategoryService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $jobListDetailsCategoryData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return JobListDetailsCategoryResource::collection($jobListDetailsCategoryData);
    }

    public function store(JobListDetailsCategoryRequest $request): JsonResponse
    {
        $jobListDetailsCategory = $this->service->createJobListDetailsCategory($request->validated());

        return apiSuccess(
            new JobListDetailsCategoryResource($jobListDetailsCategory),
            'created'
        );
    }

    public function show(JobListDetailsCategory $jobListDetailsCategory): JobListDetailsCategoryResource
    {
        return new JobListDetailsCategoryResource(
            $this->service->getJobListDetailsCategory($jobListDetailsCategory)
        );
    }

    public function update(JobListDetailsCategoryRequest $request, JobListDetailsCategory $jobListDetailsCategory): JsonResponse
    {
        $jobListDetailsCategory = $this->service->updateJobListDetailsCategory(
            $jobListDetailsCategory,
            $request->validated()
        );

        return apiSuccess(
            new JobListDetailsCategoryResource($jobListDetailsCategory),
            'updated'
        );
    }

    public function destroy(JobListDetailsCategory $jobListDetailsCategory): JsonResponse
    {
        $this->service->deleteJobListDetailsCategory($jobListDetailsCategory);

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
}
