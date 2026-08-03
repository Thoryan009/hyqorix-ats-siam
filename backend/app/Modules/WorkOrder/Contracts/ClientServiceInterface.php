<?php
namespace App\Modules\WorkOrder\Contracts;

interface ClientServiceInterface
{
    public function getClients(): array;
    public function getClientById(int $id);
}
