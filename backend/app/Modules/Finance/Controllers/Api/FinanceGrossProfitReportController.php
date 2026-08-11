<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\FinanceGrossProfitReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class FinanceGrossProfitReportController extends Controller
{
    public function __construct(
        private readonly FinanceGrossProfitReportService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return apiSuccess($this->service->getReport($this->filters($request), false));
    }

    public function exportCsv(Request $request): JsonResponse
    {
        $filename = 'gross-profit-report-' . now()->format('Y-m-d') . '.csv';

        return apiSuccess([
            'content' => $this->service->exportCsv($this->filters($request)),
            'filename' => $filename,
        ]);
    }

    public function exportPdf(Request $request): JsonResponse
    {
        try {
            $filename = 'gross-profit-report-' . now()->format('Y-m-d') . '.pdf';

            return apiSuccess([
                'content' => base64_encode($this->service->exportPdf($this->filters($request))),
                'filename' => $filename,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Failed to generate PDF.',
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return [
            'job_id' => $this->optional($request, 'job_id') ?? $this->optional($request, 'job_list_id'),
            'job_list_ids' => $request->input('job_list_ids', $request->input('job_list_ids[]')),
            'client_id' => $this->optional($request, 'client_id'),
            'client_ids' => $request->input('client_ids', $request->input('client_ids[]')),
            'work_order_id' => $this->optional($request, 'work_order_id'),
            'work_order_ids' => $request->input('work_order_ids', $request->input('work_order_ids[]')),
            'agent_id' => $this->optional($request, 'agent_id'),
            'country_id' => $this->optional($request, 'country_id'),
            'principal_id' => $this->optional($request, 'principal_id'),
            'from_date' => $this->optional($request, 'from_date'),
            'to_date' => $this->optional($request, 'to_date'),
        ];
    }

    private function optional(Request $request, string $key): mixed
    {
        $value = $request->input($key);

        if ($value === null || $value === '' || $value === 'null' || $value === 'undefined') {
            return null;
        }

        return $value;
    }
}
