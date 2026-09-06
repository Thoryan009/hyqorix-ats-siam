<?php

namespace App\Modules\Journals\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Journals\Requests\PartyLedgerIndexRequest;
use App\Modules\Journals\Services\PartyLedgerService;
use Illuminate\Http\JsonResponse;

class PartyLedgerController extends Controller
{
    public function __construct(
        private readonly PartyLedgerService $service
    ) {}

    public function index(PartyLedgerIndexRequest $request): JsonResponse
    {
        $paginator = $this->service->getPaginatedLedger($request->filters());

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'links' => $paginator->linkCollection()->toArray(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'success' => true,
            'message' => 'Party ledger fetched successfully.',
        ]);
    }

    public function export(PartyLedgerIndexRequest $request): JsonResponse
    {
        $rows = $this->service->getExportLedger($request->filters());

        return response()->json([
            'data' => $rows,
            'meta' => [
                'total' => $rows->count(),
            ],
            'success' => true,
            'message' => 'Party ledger export fetched successfully.',
        ]);
    }
}
