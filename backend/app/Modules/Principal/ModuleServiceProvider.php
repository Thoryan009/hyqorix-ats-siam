<?php

namespace App\Modules\Principal;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Principal\Contracts\PrincipalDataServiceInterface;
use App\Modules\Principal\Services\PrincipalDataDbService;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PrincipalDataServiceInterface::class, PrincipalDataDbService::class);

        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'principal_cache'
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
