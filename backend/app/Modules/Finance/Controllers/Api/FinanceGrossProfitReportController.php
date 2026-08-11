<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\FinanceGrossProfitReportService;
use App\Modules\Setting\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceGrossProfitReportController extends Controller
{
    public function __construct(
        private readonly FinanceGrossProfitReportService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return apiSuccess($this->service->getReport($this->filters($request), false));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $csv = $this->service->exportCsv($this->filters($request));
        $fileName = 'gross-profit-report-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->service->getReport($this->filters($request), true);
        $setting = Setting::query()->find(1);

        $pdf = Pdf::loadView('finance.gross-profit-report-pdf', [
            'expenseHeads' => $report['expense_heads'] ?? [],
            'rows' => $report['rows'] ?? [],
            'summary' => $report['summary'] ?? [],
            'filters' => $this->filters($request),
            'setting' => [
                'company_name' => $setting?->company_name,
                'company_address' => $setting?->company_address,
                'company_logo_path' => $setting?->company_logo_path,
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('gross-profit-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return [
            'job_id' => $request->input('job_id', $request->input('job_list_id')),
            'job_list_ids' => $request->input('job_list_ids', $request->input('job_list_ids[]')),
            'client_id' => $request->input('client_id'),
            'client_ids' => $request->input('client_ids', $request->input('client_ids[]')),
            'work_order_id' => $request->input('work_order_id'),
            'work_order_ids' => $request->input('work_order_ids', $request->input('work_order_ids[]')),
            'agent_id' => $request->input('agent_id'),
            'country_id' => $request->input('country_id'),
            'principal_id' => $request->input('principal_id'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ];
    }
}
