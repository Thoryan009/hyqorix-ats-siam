<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\FinanceBank;
use App\Modules\Finance\Requests\FinanceBankRequest;
use App\Modules\Finance\Resources\FinanceBankResource;
use App\Modules\Finance\Services\FinanceBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceBankController extends Controller
{
    public function __construct(
        private readonly FinanceBankService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');

        return FinanceBankResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(FinanceBankRequest $request): JsonResponse
    {
        $bank = $this->service->createFinanceBank($request->validated());

        return apiSuccess(
            new FinanceBankResource($bank),
            'created',
            200,
            'Bank'
        );
    }

    public function show(FinanceBank $financeBank): FinanceBankResource
    {
        return new FinanceBankResource(
            $this->service->getFinanceBank($financeBank)
        );
    }

    public function update(FinanceBankRequest $request, FinanceBank $financeBank): JsonResponse
    {
        $bank = $this->service->updateFinanceBank($financeBank, $request->validated());

        return apiSuccess(
            new FinanceBankResource($bank),
            'updated',
            200,
            'Bank'
        );
    }

    public function destroy(FinanceBank $financeBank): JsonResponse
    {
        $this->service->deleteFinanceBank($financeBank);

        return apiSuccess(null, 'deleted', 200, 'Bank');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $this->service->bulkDelete($request->ids);

        return apiSuccess(null, 'deleted', 200, 'Banks');
    }
}
