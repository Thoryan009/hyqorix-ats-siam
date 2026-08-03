<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Requests\FinanceIncomeCollectionRequest;
use App\Modules\Finance\Resources\FinanceIncomeCollectionResource;
use App\Modules\Finance\Services\FinanceIncomeCollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceIncomeCollectionController extends Controller
{
    public function __construct(
        private readonly FinanceIncomeCollectionService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['category_id'] = $request->get('category_id');
        $filters['head_id'] = $request->get('head_id');
        $filters['from_date'] = $request->get('from_date');
        $filters['to_date'] = $request->get('to_date');

        return FinanceIncomeCollectionResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(FinanceIncomeCollectionRequest $request): JsonResponse
    {
        $collection = $this->service->collect($request->validated());

        return apiSuccess(
            new FinanceIncomeCollectionResource($collection),
            'created'
        );
    }
}
