<?php

namespace Jisan\LaravelReadyModular;

use Illuminate\Support\ServiceProvider;
use Jisan\LaravelReadyModular\Console\ModuleSeed;
use Jisan\LaravelReadyModular\Console\ModuleInstall;
use Jisan\LaravelReadyModular\Console\MakeModuleCommand;
use Jisan\LaravelReadyModular\Console\MakeSubEntityCommand;
/*
|--------------------------------------------------------------------------
| Generators
|--------------------------------------------------------------------------
*/

use Jisan\LaravelReadyModular\Generator\ModuleGenerator;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\ModuleTypes\CrudModule;
use Jisan\LaravelReadyModular\Generator\ServiceTypes\CacheService;
use Jisan\LaravelReadyModular\Generator\ServiceTypes\PlainService;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\ModelGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\RouteGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\RequestGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Common\FolderGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\ResourceGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\RepositoryGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\MigrationGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\ControllerGenerator;
use Jisan\LaravelReadyModular\Generator\Support\LazyGeneratorWrapper;
use Jisan\LaravelReadyModular\Generator\Generators\Common\ServiceProviderGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\CacheGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\DbServiceGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\InterfaceGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\SchemaGenerator;
use Jisan\LaravelReadyModular\Generator\Generators\Crud\SeederGenerator;
use Jisan\LaravelReadyModular\Generator\ModuleTypes\AuthModule;
use Jisan\LaravelReadyModular\Generator\ModuleTypes\SettingModule;

class LaravelReadyModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Lightweight bindings only
        $this->app->singleton(Filesystem::class);
        $this->app->singleton(PlainService::class);
        $this->app->singleton(CacheService::class);

        // CRUD Module
        $this->app->singleton(CrudModule::class, function ($app) {
            return new CrudModule(
                generators: $this->makeModuleGenerators($app)
            );
        });
        // Auth Module
        $this->app->singleton(AuthModule::class, function ($app) {
            return new AuthModule(
                generators: $this->makeModuleGenerators($app)
            );
        });

        // Setting Module
        $this->app->singleton(SettingModule::class, function ($app) {
            return new SettingModule(
                generators: $this->makeModuleGenerators($app)
            );
        });

        // Lazy main generator
        $this->app->singleton(ModuleGenerator::class, function ($app) {
            return new ModuleGenerator(
                modules: [
                    'crud' => $app->make(CrudModule::class),
                    'auth' => $app->make(AuthModule::class),
                    'setting' => $app->make(SettingModule::class)
                ],
                services: [
                    'plain' => $app->make(PlainService::class),
                    'cache' => $app->make(CacheService::class),
                ]
            );
        });
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Register commands only when running in console
        $this->commands([
            MakeModuleCommand::class,
            MakeSubEntityCommand::class,
            ModuleInstall::class,
            ModuleSeed::class,
        ]);
    }

     /**
     * Return the common generators for CRUD/Auth modules
     */
    private function makeModuleGenerators($app): array
    {
        return [
            new LazyGeneratorWrapper(fn() => $app->make(FolderGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(ServiceProviderGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(ModelGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(CacheGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(MigrationGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(RequestGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(SeederGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(ResourceGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(SchemaGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(RepositoryGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(ControllerGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(RouteGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(InterfaceGenerator::class)),
            new LazyGeneratorWrapper(fn() => $app->make(DbServiceGenerator::class)),
        ];
    }
}
