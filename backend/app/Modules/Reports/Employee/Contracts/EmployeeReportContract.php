<?php

namespace App\Modules\Reports\Employee\Contracts;

interface EmployeeReportContract
{
    public function generate(array $filters): array;
}
