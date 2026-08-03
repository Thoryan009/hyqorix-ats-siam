<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Application\Requests\ProcessRequest;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Resources\ProcessResource;
use App\Modules\Application\Services\ProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProcessController extends Controller
{
    public function __construct(
        private readonly ProcessService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
         $data = $this->service->getPaginatedDataWithCache(
            $filters['page'],
            $filters['per_page'],
            $filters
        );
        return ProcessResource::collection($data);
    }

    public function show(int $id): ProcessResource
    {
        return new ProcessResource(
            $this->service->getById($id)
        );
    }

    public function update(ProcessRequest $request, int $id): JsonResponse
    {
        $record = $this->service->update($id, $request->validated());
        return apiSuccess(
            new ProcessResource($record),
            'updated',
            200,
            'Process'
        );
    }

}
