<?php

namespace Jisan\LaravelReadyModular\Generator\Support;

class Stub
{
    public static function path(string $name): string
    {
        return base_path(
            "packages/jisan/laravel-ready-modular/resources/stubs/{$name}.stub.php"
        );
    }

    public static function get(string $name): string
    {
        $path = self::path($name);

        if (! file_exists($path)) {
            throw new \RuntimeException("Stub file not found: {$path}");
        }

        return file_get_contents($path);
    }
}
