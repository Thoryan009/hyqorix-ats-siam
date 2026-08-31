<?php

namespace App\Modules\Journals\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Journals\Requests\GeneralLedgerIndexRequest;
use App\Modules\Journals\Services\GeneralLedgerService;
use Illuminate\Http\JsonResponse;

class GeneralLedgerController extends Controller
{
    public function __construct(
        private readonly GeneralLedgerService $service
    ) {}

    public function index(GeneralLedgerIndexRequest $request): JsonResponse
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
            'message' => 'General ledger fetched successfully.',
        ]);
    }
}
