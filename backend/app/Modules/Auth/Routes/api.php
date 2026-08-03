<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\Api\AuthController;
use App\Modules\Auth\Controllers\Api\DashboardController;
use App\Modules\Auth\Controllers\Api\PermissionController;
use App\Modules\Auth\Controllers\Api\RoleController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/flight-summary', [DashboardController::class, 'flightSummary'])->middleware('permission:flight_summary.view');
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/user', [AuthController::class, 'user']);

    // Change Password
    Route::post('auth/password-change', [AuthController::class, 'passwordChange']);
});

Route::prefix('access-control')->middleware('auth:sanctum')->group(function () {
    Route::crud('roles', RoleController::class, 'role');

    Route::post('permissions/generate-module', [PermissionController::class, 'storeModule'])
        ->middleware('permission:permission.create');
    Route::crud('permissions', PermissionController::class, 'permission');

    Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->middleware('permission:role.edit');
});
