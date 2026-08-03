<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\ExpenseCategory;
use App\Modules\Finance\Requests\ExpenseCategoryRequest;
use App\Modules\Finance\Resources\ExpenseCategoryResource;
use App\Modules\Finance\Services\ExpenseCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        private readonly ExpenseCategoryService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');

        return ExpenseCategoryResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(ExpenseCategoryRequest $request): JsonResponse
    {
        $category = $this->service->createExpenseCategory($request->validated());

        return apiSuccess(
            new ExpenseCategoryResource($category),
            'created'
        );
    }

    public function show(ExpenseCategory $expenseCategory): ExpenseCategoryResource
    {
        return new ExpenseCategoryResource(
            $this->service->getExpenseCategory($expenseCategory)
        );
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $category = $this->service->updateExpenseCategory($expenseCategory, $request->validated());

        return apiSuccess(
            new ExpenseCategoryResource($category),
            'updated'
        );
    }
}
