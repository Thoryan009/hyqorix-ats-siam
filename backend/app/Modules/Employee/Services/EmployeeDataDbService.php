<?php

namespace App\Modules\Employee\Services;

use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use App\Modules\Dhaka\Models\Dhaka;
use App\Modules\Employee\Contracts\EmployeeDataServiceInterface;
use App\Modules\Employee\Models\Department;
use App\Modules\Employee\Models\Designation;
use Illuminate\Support\Facades\Cache;

class EmployeeDataDbService implements EmployeeDataServiceInterface
{
    public $cacheKey = "Employee_data_all";

    public function getEmployeeData(): array
    {
        $cacheKey = $this->cacheKey;

        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () {
             $roles = Role::query(); // fetch from DB
             $roles = $roles->get()->map(function ($role) {
                 return [
                     'id' => $role->id,
                     'name' => $role->name,
                 ];
             })->toArray();

             $designations = Designation::query()->get()->map(function ($designation) {
                 return [
                     'id' => $designation->id,
                     'name' => $designation->name,
                 ];
             })->toArray();

             $departments =  Department::query()->get()->map(function ($department) {
                 return [
                     'id' => $department->id,
                     'name' => $department->name,
                 ];
             })->toArray();

             $statuses = User::STATUSES;
             $statuses = collect($statuses)->map(function ($status) {
                 return [
                     'id' => $status,
                     'name' => $status == 1 ? 'Active' : 'Inactive',

                 ];
             })->toArray();

            return [
                'roles' => $roles,
                'designations' => $designations,
                'statuses' => $statuses,
                'departments' => $departments,
            ];
        });
    }


    public function clearEmployeeDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
