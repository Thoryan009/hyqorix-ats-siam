<?php

namespace App\Modules\Agent;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Agent\Contracts\AgentDataServiceInterface;
use App\Modules\Agent\Services\AgentDataDbService;


class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AgentDataServiceInterface::class, AgentDataDbService::class);

        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'agent_cache'
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
