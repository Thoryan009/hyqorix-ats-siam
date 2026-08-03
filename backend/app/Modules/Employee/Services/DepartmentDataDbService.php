<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Contracts\DepartmentDataServiceInterface;
use Illuminate\Support\Facades\Cache;

class DepartmentDataDbService implements DepartmentDataServiceInterface
{
    public $cacheKey = "Department_data_all";

    public function getDepartmentData(): array
    {
        $cacheKey = $this->cacheKey;

        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $employees = Employee::query(); // fetch from DB

             $employees = $employees->get()->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                ];
            })->toArray();

            return [
                'employees' => $employees,
            ];
        });
    }


    public function clearDepartmentDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
