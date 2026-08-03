<?php

namespace App\Modules\Reports\Expiry\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportIndexRequest;
use App\Modules\Reports\Expiry\Services\ExpiryReportService;
use Illuminate\Http\Request;

class ExpiryReportController extends Controller
{
    public function index(ReportIndexRequest $request, ExpiryReportService $service)
    {
        $fromDate = $request->get('from_date') ?: now()->toDateString();
        $toDate = $request->get('to_date') ?: $fromDate;

        $filters = [
            'process' => $request->get('process'),
            'search' => $request->get('search'),
            'client_id' => $request->get('client_id'),
            'agent_id' => $request->get('agent_id'),
            'job_id' => $request->get('job_id'),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'page' => (int) ($request->get('page', 1)),
            'per_page' => (int) ($request->get('per_page', 10)),
        ];
        return $service->getExpiryReport($filters);
    }

    public function filterData(ReportIndexRequest $request, ExpiryReportService $service)
    {
        $fromDate = $request->get('from_date') ?: now()->toDateString();
        $toDate = $request->get('to_date') ?: $fromDate;

        return $service->getFilterData([
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'search' => $request->get('search'),
            'client_id' => $request->get('client_id'),
            'agent_id' => $request->get('agent_id'),
            'job_id' => $request->get('job_id'),
        ]);
    }

    public function exportCSV(Request $request, ExpiryReportService $service)
    {
        $filters = [
            'process' => $request->get('process'),
            'search' => $request->get('search'),
            'client_id' => $request->get('client_id'),
            'agent_id' => $request->get('agent_id'),
            'job_id' => $request->get('job_id'),
            'from_date' => $request->get('from_date') ?: now()->toDateString(),
            'to_date' => $request->get('to_date') ?: now()->toDateString(),
        ];

        return response()->streamDownload(function () use ($service, $filters) {
            echo $service->exportCSV($filters);
        }, 'expiry_report.csv');
    }
}
