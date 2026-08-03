<?php

namespace App\Modules\Reports\ATS\Contracts;

interface ATSReportContract
{
    public function generate(array $filters);

}
