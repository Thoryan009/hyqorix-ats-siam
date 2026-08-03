<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Common;

use Illuminate\Support\Str;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class ServiceProviderGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'service_provider';
    }

    public function shouldRun(ModuleContext $context): bool
    {
        return !$context->isSubEntity && !$context->isShortEntity;
    }

    public function generate(ModuleContext $context): void
    {


        $moduleName      = $context->name;
        $moduleNameLower = Str::lower($moduleName);

        $stub = <<<PHP
<?php

namespace App\Modules\\{$moduleName};

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\\{$moduleName}\Services\\{$moduleName}DataDbService;
use App\Modules\\{$moduleName}\Contracts\\{$moduleName}DataServiceInterface;


class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        \$bindings = [
             {$moduleName}DataServiceInterface::class => {$moduleName}DataDbService::class
        ];

        foreach (\$bindings as \$interface => \$implementation) {
            \$this->app->bind(\$interface, \$implementation);
        }

        \$this->mergeConfigFrom(
            __DIR__ . '/Config/cache.php',
            '{$moduleNameLower}_cache'
        );
    }

    public function boot(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'auth:sanctum'])
            ->group(function () {
                \$this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
            });

        \$this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }
}
PHP;

        $this->fs->put(
            "{$context->path}/ModuleServiceProvider.php",
            $stub
        );
    }
}
