<?php

namespace App\Modules\Reports\Transaction\Contracts;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface TransactionReportContract
{
    public function generate(array $filters);
}
