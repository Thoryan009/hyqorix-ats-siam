<?php

namespace App\Modules\Document;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/cache.php', 'document_cache');
        $this->mergeConfigFrom(__DIR__ . '/Config/categories.php', 'document.categories');
    }

    public function boot(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'auth:sanctum'])
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/api.php'));

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }
}
