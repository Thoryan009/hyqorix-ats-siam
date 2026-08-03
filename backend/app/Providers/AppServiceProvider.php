<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        foreach (glob($modulesPath . '/*/ModuleServiceProvider.php') as $provider) {
            $module = basename(dirname($provider));
            $providerClass = "App\\Modules\\{$module}\\ModuleServiceProvider";
            app()->register($providerClass);
        }
    }
}
