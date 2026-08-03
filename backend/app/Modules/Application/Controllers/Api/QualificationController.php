<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Application\Models\Qualification;
use App\Modules\Application\Requests\QualificationRequest;
use App\Modules\Application\Resources\QualificationResource;
use App\Modules\Application\Services\QualificationService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QualificationController extends Controller
{
    public function __construct(
        private readonly QualificationService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $qualificationData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return QualificationResource::collection($qualificationData);
    }

    public function store(QualificationRequest $request): JsonResponse
    {
        $qualification = $this->service->createQualification($request->validated());

        return apiSuccess(
            new QualificationResource($qualification),
            'created'
        );
    }

    public function show(Qualification $qualification): QualificationResource
    {
        return new QualificationResource(
            $this->service->getQualification($qualification)
        );
    }

    public function update(QualificationRequest $request, Qualification $qualification): JsonResponse
    {
        $qualification = $this->service->updateQualification(
            $qualification,
            $request->validated()
        );

        return apiSuccess(
            new QualificationResource($qualification),
            'updated'
        );
    }

    public function destroy(Qualification $qualification): JsonResponse
    {
        $this->service->deleteQualification($qualification);

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
