<?php
namespace App\Modules\Application\Contracts;

interface TransactionDataServiceInterface
{
    public function getTransactionData(): array;
}
