<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\FinanceBillEntry;
use App\Modules\Finance\Requests\FinanceBillEntryBatchRequest;
use App\Modules\Finance\Requests\FinanceBillEntryMultiHeadRequest;
use App\Modules\Finance\Requests\FinanceBillEntryManagerApproveBatchRequest;
use App\Modules\Finance\Requests\FinanceBillEntryManagerApproveRequest;
use App\Modules\Finance\Requests\FinanceBillEntryPayPayableRequest;
use App\Modules\Finance\Requests\FinanceBillEntryRequest;
use App\Modules\Finance\Requests\FinanceBillEntryUpdateRequest;
use App\Modules\Finance\Resources\FinanceBillEntryResource;
use App\Modules\Finance\Services\FinanceBillEntryService;
use App\Modules\Shared\Helpers\FileHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceBillEntryController extends Controller
{
    public function __construct(
        private readonly FinanceBillEntryService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');
        $filters['category_id'] = $request->get('category_id');
        $filters['head_id'] = $request->get('head_id');

        return FinanceBillEntryResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function summary(): JsonResponse
    {
        return apiSuccess($this->service->getSummary());
    }

    public function headTotal(int $headId): JsonResponse
    {
        return apiSuccess([
            'head_id' => $headId,
            'total_amount' => $this->service->getTotalPaidByHead($headId),
        ]);
    }

    public function store(FinanceBillEntryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->applyStoredReceiptPaths($request, $data);

        $entry = $this->service->createBillEntry($data);

        return apiSuccess(
            new FinanceBillEntryResource($entry->load(['expenseCategory', 'expenseHead', 'assetAccount', 'vendorAccount'])),
            'created'
        );
    }

    public function storeBatch(FinanceBillEntryBatchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->applyStoredReceiptPaths($request, $data);

        $entries = $this->service->createBillEntryBatch($data);

        return apiSuccess(
            FinanceBillEntryResource::collection(collect($entries)),
            'created'
        );
    }

    public function storeMultiHead(FinanceBillEntryMultiHeadRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->applyStoredReceiptPaths($request, $data);

        $entries = $this->service->createMultiHeadBillEntries($data);

        return apiSuccess(
            FinanceBillEntryResource::collection(collect($entries)),
            'created'
        );
    }

    public function show(FinanceBillEntry $financeBillEntry): FinanceBillEntryResource
    {
        return new FinanceBillEntryResource(
            $this->service->getBillEntry($financeBillEntry)
        );
    }

    public function update(FinanceBillEntryUpdateRequest $request, FinanceBillEntry $financeBillEntry): JsonResponse
    {
        $entry = $this->service->updateBillEntry($financeBillEntry, $request->validated());

        return apiSuccess(
            new FinanceBillEntryResource($entry),
            'updated'
        );
    }

    public function approve(FinanceBillEntryUpdateRequest $request, FinanceBillEntry $financeBillEntry): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('manual_approval_path')) {
            $data['manual_approval_path'] = FileHelper::store(
                $request->file('manual_approval_path'),
                'bill-manual-approvals'
            );
        } else {
            unset($data['manual_approval_path']);
        }

        $entry = $this->service->approveBillEntry($financeBillEntry, $data);

        return apiSuccess(
            new FinanceBillEntryResource($entry),
            'updated'
        );
    }

    public function payPayable(
        FinanceBillEntryPayPayableRequest $request,
        FinanceBillEntry $financeBillEntry
    ): JsonResponse {
        $entry = $this->service->payPayableBillEntry($financeBillEntry, $request->validated());

        return apiSuccess(
            new FinanceBillEntryResource($entry),
            'updated'
        );
    }

    public function managerApprove(
        FinanceBillEntryManagerApproveRequest $request,
        FinanceBillEntry $financeBillEntry
    ): JsonResponse {
        $entry = $this->service->managerApproveBillEntry($financeBillEntry, $request->validated());

        return apiSuccess(
            new FinanceBillEntryResource($entry),
            'updated'
        );
    }

    public function managerApproveBatch(FinanceBillEntryManagerApproveBatchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $entries = $this->service->managerApproveBillEntryBatch($data['ids'], $data);

        return apiSuccess(
            collect($entries)
                ->map(fn (FinanceBillEntry $entry) => (new FinanceBillEntryResource($entry))->resolve())
                ->values()
                ->all(),
            'updated'
        );
    }

    public function reject(FinanceBillEntryUpdateRequest $request, FinanceBillEntry $financeBillEntry): JsonResponse
    {
        $entry = $this->service->rejectBillEntry($financeBillEntry, $request->validated());

        return apiSuccess(
            new FinanceBillEntryResource($entry),
            'updated'
        );
    }

    private function applyStoredReceiptPaths($request, array &$data): void
    {
        if (!$request->hasFile('receipt_path')) {
            unset($data['receipt_path']);

            return;
        }

        $files = $request->file('receipt_path');
        if (!is_array($files)) {
            $files = [$files];
        }

        $paths = [];
        foreach ($files as $file) {
            if ($file) {
                $paths[] = FileHelper::store($file, 'bill-receipts');
            }
        }

        if ($paths === []) {
            unset($data['receipt_path']);

            return;
        }

        $data['receipt_path'] = $paths;
    }
}
