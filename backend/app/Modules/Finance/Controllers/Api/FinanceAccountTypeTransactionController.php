<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Requests\FinanceAccountTypeTransactionRequest;
use App\Modules\Finance\Resources\FinanceAccountTypeTransactionResource;
use App\Modules\Finance\Services\FinanceAccountTypeTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceAccountTypeTransactionController extends Controller
{
    public function __construct(
        private readonly FinanceAccountTypeTransactionService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['transaction_type'] = $request->get('transaction_type');

        return FinanceAccountTypeTransactionResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(FinanceAccountTypeTransactionRequest $request): JsonResponse
    {
        $transaction = $this->service->submitTransaction($request->validated());

        return apiSuccess(
            new FinanceAccountTypeTransactionResource($transaction),
            'created'
        );
    }
}
