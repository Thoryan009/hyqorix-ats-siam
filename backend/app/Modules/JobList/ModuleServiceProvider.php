<?php

namespace App\Modules\JobList;

use App\Modules\Application\Contracts\JobListServiceInterface;
use App\Modules\Application\Services\JobListDbService;
use App\Modules\JobList\Contracts\AtsDataServiceInterface;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Modules\JobList\Events\BulkNextProcessCreated;
use App\Modules\JobList\Listeners\CreateBulkNextProcess;
use App\Modules\JobList\Services\AtsDataDbService;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            'joblist_cache'
        );

        // ✅ Bind Interface to Implementation
        $this->app->bind(
            JobListServiceInterface::class,
            JobListDbService::class
        );
        $this->app->bind(
            AtsDataServiceInterface::class,
            AtsDataDbService::class
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

    // Register Event Listener
    Event::listen(
        BulkNextProcessCreated::class,
        CreateBulkNextProcess::class
    );
}
}
