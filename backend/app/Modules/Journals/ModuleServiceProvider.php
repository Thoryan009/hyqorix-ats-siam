<?php

namespace App\Modules\Journals;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'auth:sanctum'])
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/api.php'));

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }
}
