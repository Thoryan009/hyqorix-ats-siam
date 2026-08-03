<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\IncomeCategory;
use App\Modules\Finance\Requests\IncomeCategoryRequest;
use App\Modules\Finance\Resources\IncomeCategoryResource;
use App\Modules\Finance\Services\IncomeCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IncomeCategoryController extends Controller
{
    public function __construct(
        private readonly IncomeCategoryService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');

        return IncomeCategoryResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function show(IncomeCategory $incomeCategory): IncomeCategoryResource
    {
        return new IncomeCategoryResource(
            $this->service->getIncomeCategory($incomeCategory)
        );
    }

    public function update(IncomeCategoryRequest $request, IncomeCategory $incomeCategory): JsonResponse
    {
        $category = $this->service->updateIncomeCategory($incomeCategory, $request->validated());

        return apiSuccess(
            new IncomeCategoryResource($category),
            'updated'
        );
    }
}
