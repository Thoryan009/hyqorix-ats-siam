<?php

namespace App\Modules\Client;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Client\Contracts\ClientDataServiceInterface;
use App\Modules\Client\Services\ClientDataDbService;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ClientDataServiceInterface::class,
            ClientDataDbService::class
        );
        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'client_cache'
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
