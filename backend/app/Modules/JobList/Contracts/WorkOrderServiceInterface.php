<?php
namespace App\Modules\JobList\Contracts;

interface WorkOrderServiceInterface
{
    public function getWorkOrders(): array;
}
