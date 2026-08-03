<?php

namespace App\Modules\Reports\ATS\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Reports\ATS\Services\ATSReportService;
use App\Http\Requests\ReportIndexRequest;
use App\Modules\Reports\ATS\Resource\ATSDashboardResource;
use App\Modules\Reports\ATS\Services\AtsSummaryReportService;

class ATSReportController extends Controller
{

    public function index(Request $request, ATSReportService $service, AtsSummaryReportService $summaryReportService)
    {
         $data = $service->getRecentDashboardDataWithSummary();

        return new ATSDashboardResource($data);

    }

     public function show(ReportIndexRequest $request, ATSReportService $service)
    {
         $type = $request->get('type'); // active | expired | expiring | null

        // base filters
        $filters = $request->all();
        $filters['process_id']      = $request->get('process_id');
        $filters['application_status'] = $request->get('application_status');
        $filters['job_list_id']     = $request->get('job_list_id');
        $filters['work_order_id']   = $request->get('work_order_id');
        $filters['client_id']       = $request->get('client_id');

        // 🔹 when type is provided → specific report
        if ($type) {
            $report = $service->resolve($type);
            return $report->generate($filters);
            // generate() already returns ATSResource::collection()
        }

        // 🔹 initial hit → all applications whereHas process
        return $service->getAllAtsReport($filters);


    }

    public function exportCSV(ReportIndexRequest $request, ATSReportService $service)
    {
        $type = $request->get('type');

        $filters = $request->all();
        $filters['process_id']    = $request->get('process_id');
        $filters['application_status'] = $request->get('application_status');
        $filters['job_list_id']   = $request->get('job_list_id');
        $filters['work_order_id'] = $request->get('work_order_id');
        $filters['client_id']     = $request->get('client_id');

        // 🔹 get data (same as show)
        if ($type) {
            $report = $service->resolve($type);
            $data = $report->query($filters)->get();
        } else {
            $data = $service->getQuery($filters)->get();
        }

        return $service->exportToCSV($data);
    }

        private function validateRequest(array $data)   : array
        {
            $rules = [
                'type'          => 'required|in:active,expired,expiring',
                'process_id'    => 'nullable|integer|exists:processes,id',
                'job_list_id'   => 'nullable|integer|exists:job_lists,id',
                'client_id'     => 'nullable|integer|exists:clients,id',
            ];

            validator($data, $rules)->validate();
            return $data;
        }

        public function getAtsSummaryReport(Request $request, AtsSummaryReportService $summaryReportService)
        {
            $filters = [
                // 'job_id'   => $request->get('job_id'),
                'client_id'     => $request->get('client_id'),
            ];

            $summaryReport = $summaryReportService->getSummary($filters);
            return response()->json($summaryReport);
        }

        public function exportAtsSummaryReportCsv(Request $request, AtsSummaryReportService $summaryReportService)
        {
            $filters = [
                'client_id'     => $request->get('client_id'),
            ];

            return $summaryReportService->exportToCSV($filters);
        }
}
