<?php

use App\Modules\Employee\Controllers\Api\DesignationController;
use App\Modules\Employee\Controllers\Api\EmployeeController;
use App\Modules\Employee\Controllers\Api\DepartmentController;
use Illuminate\Support\Facades\Route;


Route::crud('employees', EmployeeController::class, 'employee');
Route::get('/employee-approval-managers', [EmployeeController::class, 'getApprovalManagers']);
Route::get('/employees-all-data-with-points', [EmployeeController::class, 'getAllEmployeesWithPoints'])->middleware('permission:employee.view');
Route::get('/employees-top-performer', [EmployeeController::class, 'getTopEmployeeByPoints'])->middleware('permission:employee.view');

Route::crud('designations', DesignationController::class, 'designation');

Route::get('employee-designations', [EmployeeController::class, 'getEmployeeDesignations'])
    ->middleware('permission:employee.view');

Route::crud('departments', DepartmentController::class, 'department');
