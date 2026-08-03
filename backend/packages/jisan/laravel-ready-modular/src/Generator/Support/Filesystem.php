<?php

namespace Jisan\LaravelReadyModular\Generator\Support;

use Illuminate\Support\Facades\File;

class Filesystem
{
    public function exists(string $path): bool
    {
        return File::exists($path);
    }

    public function makeDir(string $path): void
    {
        File::makeDirectory($path, 0755, true, true);
    }

    public function put(string $path, string $content): void
    {
        File::put($path, $content);
    }

    public function get(string $path): string
    {
        return File::get($path);
    }
}
