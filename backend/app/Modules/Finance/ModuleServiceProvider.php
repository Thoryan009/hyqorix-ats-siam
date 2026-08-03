<?php

namespace App\Modules\Finance;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/cache.php', 'finance_cache');
        $this->mergeConfigFrom(__DIR__ . '/Config/accounts.php', 'finance_accounts');
    }

    public function boot(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'auth:sanctum'])
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/api.php'));

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }
}
