<?php

namespace App\Modules\Reports\Transaction\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Reports\Transaction\Services\TransactionReportService;
use App\Modules\Reports\Transaction\Resources\TransactionDashboardResource;

class TransactionReportController extends Controller
{
    public function index(Request $request, TransactionReportService $service)
    {
        $data = $service->getRecentDashboardDataWithSummary();
        return new TransactionDashboardResource($data);

    }

    public function show(Request $request, TransactionReportService $service)
    {
        $type                           = $request->get('type'); // candidate | client
        $filters                        = $request->all();
        $filters['payment_method']      = $request->get('payment_method');
        $filters['status']              = $request->get('status');
        $filters['job_list_id']         = $request->get('job_list_id');
        $filters['work_order_id']       = $request->get('work_order_id');
        $filters['client_id']           = $request->get('client_id');
        $filters['payment_from']        = $request->get('payment_from');
        $filters['payment_to']          = $request->get('payment_to');
        
        if ($type) {
            $report = $service->resolve($type);
            return $report->generate($filters);
        }
        return $service->getAllTransactionsReport($filters);
    }
}

