<?php

namespace App\Modules\Accounts\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Accounts\Models\ChartOfAccount;
use App\Modules\Accounts\Requests\ChartOfAccountBulkDeleteRequest;
use App\Modules\Accounts\Requests\ChartOfAccountIndexRequest;
use App\Modules\Accounts\Requests\ChartOfAccountRequest;
use App\Modules\Accounts\Resources\ChartOfAccountResource;
use App\Modules\Accounts\Services\ChartOfAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChartOfAccountController extends Controller
{
    public function __construct(
        private readonly ChartOfAccountService $service
    ) {}

    public function index(ChartOfAccountIndexRequest $request): AnonymousResourceCollection
    {
        return ChartOfAccountResource::collection(
            $this->service->getPaginatedData($request->filters())
        );
    }

    public function store(ChartOfAccountRequest $request): JsonResponse
    {
        $account = $this->service->create($request->validated());

        return apiSuccess(
            new ChartOfAccountResource($account),
            'created',
            201,
            'Chart of Account'
        );
    }

    public function show(ChartOfAccount $chartOfAccount): ChartOfAccountResource
    {
        return new ChartOfAccountResource(
            $this->service->getChartOfAccount($chartOfAccount)
        );
    }

    public function update(ChartOfAccountRequest $request, ChartOfAccount $chartOfAccount): JsonResponse
    {
        $account = $this->service->update($chartOfAccount, $request->validated());

        return apiSuccess(
            new ChartOfAccountResource($account),
            'updated',
            200,
            'Chart of Account'
        );
    }

    public function destroy(ChartOfAccount $chartOfAccount): JsonResponse
    {
        $this->service->delete($chartOfAccount);

        return apiSuccess(null, 'deleted', 200, 'Chart of Account');
    }

    public function bulkDelete(ChartOfAccountBulkDeleteRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request->validated('ids'));

        return apiSuccess(null, 'deleted', 200, 'Records');
    }
}
