<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\FinanceGrossProfitReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceGrossProfitReportController extends Controller
{
    public function __construct(
        private readonly FinanceGrossProfitReportService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'job_list_ids' => $request->input('job_list_ids', $request->input('job_list_ids[]')),
            'client_ids' => $request->input('client_ids', $request->input('client_ids[]')),
            'work_order_ids' => $request->input('work_order_ids', $request->input('work_order_ids[]')),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'search' => $request->get('search'),
        ];

        return apiSuccess($this->service->getReport($filters));
    }
}
