<?php

namespace App\Modules\Journals\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Journals\Models\JournalTransactionType;
use App\Modules\Journals\Requests\JournalTransactionTypeBulkDeleteRequest;
use App\Modules\Journals\Requests\JournalTransactionTypeIndexRequest;
use App\Modules\Journals\Requests\JournalTransactionTypeRequest;
use App\Modules\Journals\Resources\JournalTransactionTypeResource;
use App\Modules\Journals\Services\JournalTransactionTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JournalTransactionTypeController extends Controller
{
    public function __construct(
        private readonly JournalTransactionTypeService $service
    ) {}

    public function index(JournalTransactionTypeIndexRequest $request): AnonymousResourceCollection
    {
        return JournalTransactionTypeResource::collection(
            $this->service->getPaginatedData($request->filters())
        );
    }

    public function options(Request $request): JsonResponse
    {
        $options = $this->service->getOptions([
            'status' => $request->query('status', 'active'),
        ])->map(fn (JournalTransactionType $type) => [
            'id' => $type->code,
            'name' => $type->name,
            'code' => $type->code,
            'status' => $type->status,
            'sort_order' => (int) $type->sort_order,
        ])->values();

        return apiSuccess($options);
    }

    public function store(JournalTransactionTypeRequest $request): JsonResponse
    {
        $transactionType = $this->service->create($request->validated());

        return apiSuccess(
            new JournalTransactionTypeResource($transactionType),
            'created',
            201,
            'Transaction Type'
        );
    }

    public function show(JournalTransactionType $journalTransactionType): JournalTransactionTypeResource
    {
        return new JournalTransactionTypeResource(
            $this->service->getTransactionType($journalTransactionType)
        );
    }

    public function update(
        JournalTransactionTypeRequest $request,
        JournalTransactionType $journalTransactionType
    ): JsonResponse {
        $transactionType = $this->service->update($journalTransactionType, $request->validated());

        return apiSuccess(
            new JournalTransactionTypeResource($transactionType),
            'updated',
            200,
            'Transaction Type'
        );
    }

    public function destroy(JournalTransactionType $journalTransactionType): JsonResponse
    {
        $this->service->delete($journalTransactionType);

        return apiSuccess(null, 'deleted', 200, 'Transaction Type');
    }

    public function bulkDelete(JournalTransactionTypeBulkDeleteRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request->validated('ids'));

        return apiSuccess(null, 'deleted', 200, 'Transaction Types');
    }
}
