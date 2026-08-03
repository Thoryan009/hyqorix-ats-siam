<?php

namespace App\Modules\Employee;

use App\Modules\Employee\Contracts\DepartmentDataServiceInterface;
use App\Modules\Employee\Contracts\EmployeeDataServiceInterface;
use App\Modules\Employee\Services\DepartmentDataDbService;
use App\Modules\Employee\Services\EmployeeDataDbService;
use DeflateContext;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            EmployeeDataServiceInterface::class,
            EmployeeDataDbService::class
        );

        $this->app->bind(
            DepartmentDataServiceInterface::class,
            DepartmentDataDbService::class
        );

        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'module_cache'
        );
    }

    public function boot()
    {
        // Load routes
        Route::prefix('api')->middleware(['api', 'auth:sanctum'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        });
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }
}
