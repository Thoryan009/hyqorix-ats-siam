<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Services;

use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Illuminate\Support\Str;

class CacheServiceGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function generate(ModuleContext $context): void
    {
        $module = $context->name;
        $camelVar = lcfirst($module); // e.g. Category -> category

        $moduleType = $context->moduleType;
        $stub = Stub::get("{$moduleType}/service.cache");

        $parentModule = $module;


        $replacements = [
            'MODEL'     => $module,
            'MODEL_VAR' => $camelVar,
            'PARENT_MODEL' => $parentModule,
        ];

        if($context->isSubEntity) {
            $parentModule = $context->parentName;
            $replacements['PARENT_MODEL'] = $parentModule;
        }

        $content = StubCompiler::compile($stub, $replacements);
        $this->ensureServiceDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Services/{$module}Service.php",
            $content
        );
    }

    private function ensureServiceDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Services";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
