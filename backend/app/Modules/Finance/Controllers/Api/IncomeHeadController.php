<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\IncomeHead;
use App\Modules\Finance\Requests\IncomeHeadRequest;
use App\Modules\Finance\Resources\IncomeHeadResource;
use App\Modules\Finance\Services\IncomeHeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IncomeHeadController extends Controller
{
    public function __construct(
        private readonly IncomeHeadService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');
        $filters['category_id'] = $request->get('category_id');

        return IncomeHeadResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(IncomeHeadRequest $request): JsonResponse
    {
        $head = $this->service->createIncomeHead($request->validated());

        return apiSuccess(
            new IncomeHeadResource($head->load('incomeCategory')),
            'created'
        );
    }

    public function show(IncomeHead $incomeHead): IncomeHeadResource
    {
        return new IncomeHeadResource(
            $this->service->getIncomeHead($incomeHead)
        );
    }

    public function update(IncomeHeadRequest $request, IncomeHead $incomeHead): JsonResponse
    {
        $head = $this->service->updateIncomeHead($incomeHead, $request->validated());

        return apiSuccess(
            new IncomeHeadResource($head->load('incomeCategory')),
            'updated'
        );
    }
}
