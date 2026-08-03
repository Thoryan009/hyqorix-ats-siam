<?php

namespace App\Modules\System;

use App\Modules\System\Contracts\ActivityLogDataServiceInterface;
use App\Modules\System\Services\ActivityLogDataDbService;
use App\Modules\System\Services\SettingService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {

        $bindings = [
            ActivityLogDataServiceInterface::class => ActivityLogDataDbService::class,

        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }

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
