<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\FinanceAccount;
use App\Modules\Finance\Requests\FinanceAccountRequest;
use App\Modules\Finance\Resources\FinanceAccountResource;
use App\Modules\Finance\Services\FinanceAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceAccountController extends Controller
{
    public function __construct(
        private readonly FinanceAccountService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');
        $filters['category'] = $request->get('category');
        $filters['account_type'] = $request->get('account_type');

        return FinanceAccountResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'category' => ['required', 'string'],
        ]);

        return apiSuccess(
            $this->service->getCategorySummaryWithCache($request->get('category')),
            'fetched',
            200,
            'Account summary'
        );
    }

    public function capital(Request $request): JsonResponse
    {
        $account = $this->service->ensureCapitalAccount(true);
        $this->service->flushCache();

        return apiSuccess(
            new FinanceAccountResource($account),
            'fetched',
            200,
            'Capital account'
        );
    }

    public function agentAdvanced(Request $request): JsonResponse
    {
        $account = $this->service->ensureAgentAdvancedAccount(true);
        $this->service->flushCache();

        return apiSuccess(
            new FinanceAccountResource($account),
            'fetched',
            200,
            'Agent Advanced account'
        );
    }

    public function sale(Request $request): JsonResponse
    {
        $account = $this->service->ensureSaleAccount(true);
        $this->service->flushCache();

        return apiSuccess(
            new FinanceAccountResource($account),
            'fetched',
            200,
            'Sale account'
        );
    }

    public function billsReceivableLedger(Request $request): JsonResponse
    {
        $account = $this->service->ensureBillsReceivableAccount(true);
        $this->service->flushCache();

        return apiSuccess(
            new FinanceAccountResource($account),
            'fetched',
            200,
            'Bills Receivable account'
        );
    }

    public function store(FinanceAccountRequest $request): JsonResponse
    {
        $account = $this->service->createFinanceAccount($request->validated());

        return apiSuccess(
            new FinanceAccountResource($account->load(['bank', 'expenseHead', 'expenseCategory', 'incomeHead', 'incomeCategory'])),
            'created',
            200,
            'Account'
        );
    }

    public function show(FinanceAccount $financeAccount): FinanceAccountResource
    {
        return new FinanceAccountResource(
            $this->service->getFinanceAccount($financeAccount)
        );
    }

    public function update(FinanceAccountRequest $request, FinanceAccount $financeAccount): JsonResponse
    {
        $account = $this->service->updateFinanceAccount($financeAccount, $request->validated());

        return apiSuccess(
            new FinanceAccountResource($account->load(['bank', 'expenseHead', 'expenseCategory', 'incomeHead', 'incomeCategory'])),
            'updated',
            200,
            'Account'
        );
    }

    public function destroy(FinanceAccount $financeAccount): JsonResponse
    {
        $this->service->deleteFinanceAccount($financeAccount);

        return apiSuccess(null, 'deleted', 200, 'Account');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $this->service->bulkDelete($request->ids);

        return apiSuccess(null, 'deleted', 200, 'Accounts');
    }
}
