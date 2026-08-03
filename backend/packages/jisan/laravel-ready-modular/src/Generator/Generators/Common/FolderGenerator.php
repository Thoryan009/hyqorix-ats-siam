<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Common;

use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;

class FolderGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function generate(ModuleContext $context): void
    {
        $folders = [
            'Contracts',
            'Config',
            'Controllers/Api',
            'Models',
            'Migrations',
            'Routes',
            'Requests',
            'Resources',
            'Repositories',
            'Services',
            'Schemas',
            'Seeders',
        ];



        foreach ($folders as $folder) {
            $this->fs->makeDir("{$context->path}/{$folder}");
        }
    }
}
