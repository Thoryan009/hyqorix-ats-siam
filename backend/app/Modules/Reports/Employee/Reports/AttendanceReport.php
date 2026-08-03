<?php

namespace App\Modules\Reports\Employee\Reports;

use App\Modules\Reports\Employee\Contracts\EmployeeReportContract;

class AttendanceReport implements EmployeeReportContract
{
    public function generate(array $filters): array
    {
        return [
            'type' => 'attendance',
            'data' => []
        ];
    }
}
