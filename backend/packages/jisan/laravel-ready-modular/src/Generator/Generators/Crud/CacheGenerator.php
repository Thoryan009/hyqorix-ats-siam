<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class CacheGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'cache';
    }

    public function shouldRun(ModuleContext $context): bool
    {
        return !$context->isSubEntity && !$context->isShortEntity;
    }

    public function generate(ModuleContext $context): void
    {
        // if ($context->isSubEntity || $context->isShortEntity) {
        //     return; // Do nothing for sub-entity or short entity
        // }


        $moduleType = $context->moduleType;
        $stub = Stub::get("{$moduleType}/cache");

        $content = StubCompiler::compile($stub, [
            'MODEL' => $context->name,
        ]);

        $this->ensureConfigDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Config/cache.php",
            $content
        );
    }

    private function ensureConfigDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Config";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
