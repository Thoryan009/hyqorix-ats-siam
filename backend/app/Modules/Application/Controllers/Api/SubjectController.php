<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Application\Models\Subject;
use App\Modules\Application\Requests\SubjectRequest;
use App\Modules\Application\Resources\SubjectResource;
use App\Modules\Application\Services\SubjectService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubjectController extends Controller
{
    public function __construct(
        private readonly SubjectService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $subjectData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return SubjectResource::collection($subjectData);
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        $subject = $this->service->createSubject($request->validated());

        return apiSuccess(
            new SubjectResource($subject),
            'created'
        );
    }

    public function show(Subject $subject): SubjectResource
    {
        return new SubjectResource(
            $this->service->getSubject($subject)
        );
    }

    public function update(SubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject = $this->service->updateSubject(
            $subject,
            $request->validated()
        );

        return apiSuccess(
            new SubjectResource($subject),
            'updated'
        );
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $this->service->deleteSubject($subject);

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
