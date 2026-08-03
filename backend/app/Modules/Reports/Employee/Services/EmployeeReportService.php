<?php

namespace App\Modules\Reports\Employee\Services;

use App\Modules\Reports\Employee\Reports\{
    AttendanceReport,
    SalaryReport,
    PerformanceReport
};
use InvalidArgumentException;

class EmployeeReportService
{
    public function resolve(string $type)
    {
        return match ($type) {
            'attendance'  => app(AttendanceReport::class),
            'salary'      => app(SalaryReport::class),
            'performance' => app(PerformanceReport::class),
            default => throw new InvalidArgumentException('Invalid employee report type'),
        };
    }
}
