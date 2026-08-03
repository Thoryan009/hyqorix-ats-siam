<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class RepositoryGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

      public function key(): string
    {
        return 'repository';
    }

    public function shouldRun(ModuleContext $context): bool
    {
        return true;
    }
    public function generate(ModuleContext $context): void
    {
        $moduleName = $context->name;
        $repositoryClass = "{$moduleName}Repository";

        $this->ensureRepositoryDirectory($context->path);

        $moduleType = $context->moduleType;
        $stub = Stub::get("{$moduleType}/repository");

        $parentModule = $moduleName;

        $replaceMents = [
            'MODEL' => $context->name,
            'PARENT_MODEL' => $parentModule,
        ];

        if ($context->isSubEntity) {
            $parentModule = $context->parentName;
            $replaceMents['PARENT_MODEL'] = $parentModule;
        }


        $content = StubCompiler::compile($stub, $replaceMents);

        $this->fs->put(
            "{$context->path}/Repositories/{$repositoryClass}.php",
            $content
        );
    }

    private function ensureRepositoryDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Repositories";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
