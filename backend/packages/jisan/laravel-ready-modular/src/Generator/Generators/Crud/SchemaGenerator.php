<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class SchemaGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'schema';
    }

    public function shouldRun(ModuleContext $context): bool
    {
        return true;
    }

    public function generate(ModuleContext $context): void
    {
        $module = $context->name;

        $moduleType = $context->moduleType;
        $stub = Stub::get("{$moduleType}/schema");

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

        $this->ensureSchemasDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Schemas/{$module}TableSchema.php",
            $content
        );
    }

    private function ensureSchemasDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Schemas";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
