<?php

namespace App\Modules\Accounts\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Accounts\Requests\GrossProfitIndexRequest;
use App\Modules\Accounts\Services\GrossProfitService;
use Illuminate\Http\JsonResponse;
use Throwable;

class GrossProfitController extends Controller
{
    public function __construct(
        private readonly GrossProfitService $service
    ) {}

    public function index(GrossProfitIndexRequest $request): JsonResponse
    {
        return apiSuccess($this->service->getReport($request->filters()));
    }

    public function exportPdf(GrossProfitIndexRequest $request): JsonResponse
    {
        try {
            $filename = 'gross-profit-breakdown-' . now()->format('Y-m-d') . '.pdf';

            return apiSuccess([
                'content' => base64_encode($this->service->exportPdf($request->filters())),
                'filename' => $filename,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Failed to generate PDF.',
            ], 500);
        }
    }
}
