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
use Illuminate\Http\Request;
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

    public function saleCollections(\Illuminate\Http\Request $request): JsonResponse
    {
        $filters = [
            'page' => (int) $request->get('page', 1),
            'per_page' => (int) $request->get('per_page', 10),
        ];

        return apiSuccess($this->service->listSaleCollections($filters), 'fetched');
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

    public function exportLedgerCsv(Request $request, FinanceAccount $financeAccount): JsonResponse
    {
        $filters = [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'search' => $request->get('search'),
        ];

        $filename = 'account-ledger-' . now()->format('Y-m-d') . '.csv';

        $content = $this->service->exportAccountLedgerCsv($financeAccount->id, $filters);

        return apiSuccess([
            'content' => $content,
            'filename' => $filename,
        ]);
    }

    public function exportLedgerPdf(Request $request, FinanceAccount $financeAccount): JsonResponse
    {
        try {
            $filters = [
                'from_date' => $request->get('from_date'),
                'to_date' => $request->get('to_date'),
                'search' => $request->get('search'),
            ];

            $filename = 'account-ledger-' . now()->format('Y-m-d') . '.pdf';

            $binary = $this->service->exportAccountLedgerPdf($financeAccount->id, $filters);

            return apiSuccess([
                'content' => base64_encode($binary),
                'filename' => $filename,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Failed to generate PDF.',
            ], 500);
        }
    }
}
