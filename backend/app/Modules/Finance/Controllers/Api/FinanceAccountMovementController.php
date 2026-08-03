<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\FinanceAccount;
use App\Modules\Finance\Requests\FinanceAccountCollectPaymentRequest;
use App\Modules\Finance\Requests\FinanceAccountMovementRequest;
use App\Modules\Finance\Resources\FinanceAccountLedgerEntryResource;
use App\Modules\Finance\Resources\FinanceAccountTransactionResource;
use App\Modules\Finance\Services\FinanceAccountMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceAccountMovementController extends Controller
{
    public function __construct(
        private readonly FinanceAccountMovementService $service
    ) {}

    public function collectPayment(FinanceAccountCollectPaymentRequest $request): JsonResponse
    {
        $result = $this->service->collectPayment($request->validated());

        return apiSuccess($result, 'created', 200, 'Payment Collection');
    }

    public function saleCollectionSummary(\Illuminate\Http\Request $request): JsonResponse
    {
        $applicationIds = collect($request->input('application_ids', []))
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        return apiSuccess(
            $this->service->getSaleCollectionSummary($applicationIds),
            'fetched'
        );
    }

    public function billsReceivable(): JsonResponse
    {
        return apiSuccess($this->service->listBillsReceivable(), 'fetched');
    }

    public function showBillReceivable(int $applicationId): JsonResponse
    {
        $item = $this->service->getBillReceivable($applicationId);
        if (!$item) {
            return response()->json([
                'data' => null,
                'message' => 'Bill receivable was not found or is already settled.',
                'success' => false,
            ], 404);
        }

        return apiSuccess($item, 'fetched');
    }

    public function transfer(FinanceAccountMovementRequest $request): JsonResponse
    {
        $transaction = $this->service->transfer($request->validated());

        return apiSuccess(
            new FinanceAccountTransactionResource($transaction),
            'created',
            200,
            'Transfer'
        );
    }

    public function deposit(FinanceAccountMovementRequest $request): JsonResponse
    {
        $transaction = $this->service->deposit($request->validated());

        return apiSuccess(
            new FinanceAccountTransactionResource($transaction),
            'created',
            200,
            'Deposit'
        );
    }

    public function withdraw(FinanceAccountMovementRequest $request): JsonResponse
    {
        $transaction = $this->service->withdraw($request->validated());

        return apiSuccess(
            new FinanceAccountTransactionResource($transaction),
            'created',
            200,
            'Withdraw'
        );
    }

    public function ledger(ApiIndexRequest $request, FinanceAccount $financeAccount): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['from_date'] = $request->get('from_date');
        $filters['to_date'] = $request->get('to_date');

        return FinanceAccountLedgerEntryResource::collection(
            $this->service->getLedgerEntries($financeAccount->id, $filters)
        );
    }
}
