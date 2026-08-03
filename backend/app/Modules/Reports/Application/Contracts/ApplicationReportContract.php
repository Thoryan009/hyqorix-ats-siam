<?php

namespace App\Modules\Reports\Application\Contracts;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface ApplicationReportContract
{
    public function generate(array $filters): AnonymousResourceCollection;
}
