<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;


class ModelGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

     public function key(): string
    {
        return 'model';
    }

    public function shouldRun(ModuleContext $context): bool
    {
        return !$context->isShortEntity;
    }

    public function generate(ModuleContext $context): void
    {
        $module = $context->name;

        $moduleType = $context->moduleType;
        $stub = Stub::get("{$moduleType}/model");

        $parentModule = $module;


        $replaceMents = [
            'MODEL' => $context->name,
            'PARENT_MODEL' => $parentModule,

        ];

        if ($context->isSubEntity) {
            $parentModule = $context->parentName;
            $replaceMents['PARENT_MODEL'] = $parentModule;
        }


        $content = StubCompiler::compile($stub, $replaceMents);

        $this->ensureModelDirectory($context->path);

        $moduleName = $context->name;

        if ($moduleType == 'auth') {
            $moduleName = 'User';
        }

        $this->fs->put(
            "{$context->path}/Models/{$moduleName}.php",
            $content
        );
    }

    private function ensureModelDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Models";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
