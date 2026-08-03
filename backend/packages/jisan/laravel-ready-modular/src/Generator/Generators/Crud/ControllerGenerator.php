<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class ControllerGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'controller';
    }
    public function shouldRun(ModuleContext $context): bool
    {
        return true;
    }

    public function generate(ModuleContext $context): void
    {
        $module = $context->name;

        $moduleType = $context->moduleType;
        $stub = Stub::get("{$moduleType}/controller.api");

        $parentModule = $module;


        $replaceMents = [
            'MODEL'        => $module,
            'PARENT_MODEL' => $parentModule,
            'LOWER_MODEL'  => lcfirst($module),
            'SERVICE'      => "{$module}Service",
            'REQUEST'      => "{$module}Request",
            'RESOURCE'     => "{$module}Resource",
        ];

        if ($context->isSubEntity) {
            $parentModule = $context->parentName;
            $replaceMents['PARENT_MODEL'] = $parentModule;
        }


        $content = StubCompiler::compile($stub, $replaceMents);


        $this->ensureControllerDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Controllers/Api/{$module}Controller.php",
            $content
        );
    }

    private function ensureControllerDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Controllers/Api";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
