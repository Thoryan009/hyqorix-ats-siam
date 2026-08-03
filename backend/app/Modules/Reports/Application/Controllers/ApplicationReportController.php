<?php

namespace App\Modules\Reports\Application\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Reports\Application\Services\ApplicationReportService;

class ApplicationReportController extends Controller
{
    public function index(ApplicationReportService $service)
    {
        $data = $service->getRecentDashboardDataWithSummary();
        return response()->json($data);
    }

    public function show(Request $request, ApplicationReportService $service)
    {
         $type = $request->get('type'); // application
        $filters = $request->all();

        $filters['country_id']      = $request->get('country_id');
        $filters['process_id']      = $request->get('process_id');
        $filters['application_status'] = $request->get('application_status');
        $filters['client_id']       = auth()->user()->type === 'client' ? auth()->user()?->client?->id : $request->get('client_id');
        $filters['agent_id']      = auth()->user()->type === 'agent' ? auth()->user()?->agent?->id : $request->get('agent_id');
        $filters['job_list_id']     = $request->get('job_list_id');
        $filters['work_order_id']   = $request->get('work_order_id');
        $filters['principal_id']   =  auth()->user()->type === 'principal' ? auth()->user()?->principal?->id : $request->get('principal_id');
        $filters['from_date']       = $request->get('from_date');
        $filters['to_date']         = $request->get('to_date');

        if ($type) {
            $report = $service->resolve($type);
            return $report->generate($filters);
        }
        return $service->getAllApplication($filters);
    }

    public function exportCSV(Request $request, ApplicationReportService $service)
    {
        $type = $request->get('type');

        $filters = $request->all();
        $filters['country_id']      = $request->get('country_id');
        $filters['process_id']      = $request->get('process_id');
        $filters['application_status'] = $request->get('application_status');
        $filters['client_id']       = auth()->user()->type === 'client' ? auth()->user()?->client?->id : $request->get('client_id');
        $filters['agent_id']      = auth()->user()->type === 'agent' ? auth()->user()?->agent?->id : $request->get('agent_id');
        $filters['job_list_id']     = $request->get('job_list_id');
        $filters['work_order_id']   = $request->get('work_order_id');
        $filters['principal_id']   =  auth()->user()->type === 'principal' ? auth()->user()?->principal?->id : $request->get('principal_id');
        $filters['from_date']       = $request->get('from_date');
        $filters['to_date']         = $request->get('to_date');

        if ($type) {
            $report = $service->resolve($type);
            $data = $report->query($filters)->get();
        }else {
            $data = $service->getQuery($filters)->get();
        }

        return $service->exportToCSV($data);


    }
}
