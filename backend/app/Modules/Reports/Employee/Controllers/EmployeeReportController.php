<?php

namespace App\Modules\Reports\Employee\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Reports\Employee\Services\EmployeeReportService;

class EmployeeReportController extends Controller
{
    public function index(Request $request, EmployeeReportService $service)
    {
        $type = $request->get('type'); // attendance, salary, performance
        $filters = $request->all();

        $report = $service->resolve($type);
        $data = $report->generate($filters);

        return response()->json($data);
    }
}
