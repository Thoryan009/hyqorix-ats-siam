<?php

namespace App\Modules\Vendor;

use App\Modules\Vendor\Contracts\VendorDataServiceInterface;
use App\Modules\Vendor\Services\VendorDataDbService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            VendorDataServiceInterface::class,
            VendorDataDbService::class
        );

        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'vendor_cache'
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
