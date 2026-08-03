<?php
namespace App\Modules\Employee\Contracts;

interface EmployeeDataServiceInterface
{
    public function getEmployeeData(): array;
    public function clearEmployeeDataCache(): void;
}
