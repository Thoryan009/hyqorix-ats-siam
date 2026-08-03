<?php

namespace App\Modules\Reports\Tasheer\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportIndexRequest;
use App\Modules\Reports\Tasheer\Services\TasheerAppointmentReportService;
use Illuminate\Http\Request;

class TasheerAppointmentReportController extends Controller
{
    public function index(ReportIndexRequest $request, TasheerAppointmentReportService $service)
    {

          $filters = [
            'client_id' => $request->get('client_id'),
            'agent_id' => $request->get('agent_id'),
            'status' => $request->get('status'),
            'job_id' => $request->get('job_id'),
            'page' => (int) ($request->get('page', 1)),
            'per_page' => (int) ($request->get('per_page', 10)),
        ];
        return $service->getReport($filters);
    }

    public function filterData(TasheerAppointmentReportService $service)
    {
        return $service->getFilterData();
    }

    public function exportCsv(TasheerAppointmentReportService $service, Request  $request)
    {
        
        $filters = [
            'client_id' => $request->get('client_id'),
            'agent_id' => $request->get('agent_id'),
            'status' => $request->get('status'),
            'job_id' => $request->get('job_id'),
            'ids' => $request->get('ids', []),
        ];

        return $service->exportToCsv($filters);
    }

    public function bulkStatusUpdate(Request $request, TasheerAppointmentReportService $service)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status');

        return $service->bulkStatusUpdate($ids, $status);
    }
}
