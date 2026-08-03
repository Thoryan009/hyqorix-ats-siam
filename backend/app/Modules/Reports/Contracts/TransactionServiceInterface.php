<?php

namespace App\Modules\Reports\Contracts;

interface TransactionServiceInterface
{
    public function getPaymentMethods(): array;
    public function getStatuses(): array;
}
