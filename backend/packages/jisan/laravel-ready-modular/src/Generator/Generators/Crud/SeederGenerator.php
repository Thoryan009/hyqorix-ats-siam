<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Jisan\LaravelReadyModular\Generator\Contracts\SkippableGenerator;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class SeederGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'seeder';
    }

      public function shouldRun(ModuleContext $context): bool
    {
        return !$context->isShortEntity;
    }

    public function generate(ModuleContext $context): void
    {
        $module = $context->name;

        $moduleType = $context->moduleType;
        $stub = Stub::get("{$moduleType}/seeder");

        $parentModule = $module;

        $replaceMents = [
            'MODEL' => $module,
            'PARENT_MODEL' => $parentModule,
        ];
        if ($context->isSubEntity) {
            $parentModule = $context->parentName;
            $replaceMents['PARENT_MODEL'] = $parentModule;
        }

        $content = StubCompiler::compile($stub, $replaceMents);


        $this->ensureSeederDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Seeders/{$module}Seeder.php",
            $content
        );
    }

    private function ensureSeederDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Seeders";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
