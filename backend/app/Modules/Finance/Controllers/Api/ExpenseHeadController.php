<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\ExpenseHead;
use App\Modules\Finance\Requests\ExpenseHeadRequest;
use App\Modules\Finance\Resources\ExpenseHeadResource;
use App\Modules\Finance\Services\ExpenseHeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseHeadController extends Controller
{
    public function __construct(
        private readonly ExpenseHeadService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');
        $filters['category_id'] = $request->get('category_id');

        return ExpenseHeadResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(ExpenseHeadRequest $request): JsonResponse
    {
        $head = $this->service->createExpenseHead($request->validated());

        return apiSuccess(
            new ExpenseHeadResource($head->load('expenseCategory')),
            'created'
        );
    }

    public function show(ExpenseHead $expenseHead): ExpenseHeadResource
    {
        return new ExpenseHeadResource(
            $this->service->getExpenseHead($expenseHead)
        );
    }

    public function update(ExpenseHeadRequest $request, ExpenseHead $expenseHead): JsonResponse
    {
        $head = $this->service->updateExpenseHead($expenseHead, $request->validated());

        return apiSuccess(
            new ExpenseHeadResource($head->load('expenseCategory')),
            'updated'
        );
    }
}
