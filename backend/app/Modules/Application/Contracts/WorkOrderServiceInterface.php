<?php

namespace App\Modules\Application\Contracts;

interface WorkOrderServiceInterface
{
    public function getWorkOrderById(int $id);
}
