<?php

namespace App\Modules\WorkOrder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'workorder_cache'
        );
    }

    public function boot(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'auth:sanctum'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
            });

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }
}
