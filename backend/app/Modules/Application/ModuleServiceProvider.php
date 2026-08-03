<?php

namespace App\Modules\Application;

use App\Modules\Application\Contracts\EmbasySubmissionDataServiceInterface;
use App\Modules\Application\Services\EmbasySubmissionDataDbService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->bind(JobListServiceInterface::class, JobListDbService::class);
        $this->app->bind(EmbasySubmissionDataServiceInterface::class, EmbasySubmissionDataDbService::class);

        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'application_cache'
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
