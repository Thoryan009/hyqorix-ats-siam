<?php

namespace App\Modules\Country;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
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
