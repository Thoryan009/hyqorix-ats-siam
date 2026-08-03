<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class InterfaceGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

     public function key(): string
    {
        return 'interface';
    }

      public function shouldRun(ModuleContext $context): bool
    {
        return true;
    }

    public function generate(ModuleContext $context): void
    {
        $module = $context->name;


        $stub = Stub::get("link/interface");

        $parentModule = $module;
        if ($context->isSubEntity) {
            $parentModule = $context->parentName;
        }

        $content = StubCompiler::compile($stub, [
            'MODEL' => $context->name,
            'PARENT_MODEL' => $parentModule,
        ]);

        $this->ensureContractsDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Contracts/{$context->name}DataServiceInterface.php",
            $content
        );
    }

    private function ensureContractsDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Contracts";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
