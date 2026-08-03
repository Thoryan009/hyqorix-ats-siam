<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Carbon\Carbon;
use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;


class MigrationGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'migration';
    }

      public function shouldRun(ModuleContext $context): bool
    {
        return !$context->isShortEntity;
    }

    public function generate(ModuleContext $context): void
    {
        $timestamp = Carbon::now()->format('Y_m_d_His');
        $moduleType = $context->moduleType;

        $table = $context->table;

        if($moduleType == 'auth'){
            $table = 'users';
        }

        $fileName = "{$timestamp}_create_{$table}_table.php";

        $stub = Stub::get("{$moduleType}/migration.create");

        $content = StubCompiler::compile($stub, [
            'TABLE' => $table,
        ]);

        $this->ensureMigrationDirectory($context->path);

        $this->fs->put(
            "{$context->path}/Migrations/{$fileName}",
            $content
        );
    }

    private function ensureMigrationDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Migrations";

        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }
}
