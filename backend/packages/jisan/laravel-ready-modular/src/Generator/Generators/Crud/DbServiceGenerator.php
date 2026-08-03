<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Illuminate\Support\Str;
use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class DbServiceGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'db_service';
    }

    public function shouldRun(ModuleContext $context): bool
    {
        return true;
    }


    public function generate(ModuleContext $context): void
    {

        $module = $context->name;

        $stub = Stub::get("link/db_service");

        $parentModule = $module;

        if ($context->isSubEntity) {
            $parentModule = $context->parentName;
        }

        $pluralParent = Str::plural($parentModule);;

        $content = StubCompiler::compile($stub, [
            'MODEL' => $context->name,
            'PARENT_MODEL' => $parentModule,
            'LOWER_PARENT_MODEL' => lcfirst($parentModule),
            'PLURAL_PARENT_MODEL' => $pluralParent,
            'plural_parent_model' => Str::snake($pluralParent),
            'model' => Str::snake($context->name),
        ]);

        $this->ensureServicesDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Services/{$context->name}DataDbService.php",
            $content
        );
    }

    private function ensureServicesDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Services";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
