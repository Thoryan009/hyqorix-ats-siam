<?php


namespace App\Modules\Reports\Employee\Reports;


use App\Modules\Reports\Employee\Contracts\EmployeeReportContract;

class SalaryReport implements EmployeeReportContract
{
    public function generate(array $filters): array
    {
        return [
            'type' => 'salary',
            'data' => []
        ];
    }
}
