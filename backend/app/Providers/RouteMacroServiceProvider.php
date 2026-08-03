<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class RouteMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::macro('crud', function (
            string $uri,
            string $controller,
            string $permissionPrefix
        ) {

            // funeral-cases → funeral_case → FuneralCase → funeralCase
            $param = Str::camel(
                Str::singular(
                    Str::replace('-', '_', $uri)
                )
            );

            Route::prefix($uri)->group(function () use ($controller, $permissionPrefix, $param) {

                Route::get('/', [$controller, 'index'])
                    ->middleware("permission:{$permissionPrefix}.view");

                Route::post('/', [$controller, 'store'])
                    ->middleware("permission:{$permissionPrefix}.create");

                Route::get("{" . $param . "}", [$controller, 'show'])
                    ->middleware("permission:{$permissionPrefix}.view");

                Route::put("{" . $param . "}", [$controller, 'update'])
                    ->middleware("permission:{$permissionPrefix}.edit");

                Route::delete("{" . $param . "}", [$controller, 'destroy'])
                    ->middleware("permission:{$permissionPrefix}.delete");

                Route::post('bulk-delete', [$controller, 'bulkDelete'])
                    ->middleware("permission:{$permissionPrefix}.delete");
            });
        });
    }
}
