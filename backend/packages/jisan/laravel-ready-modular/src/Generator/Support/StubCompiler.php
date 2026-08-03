<?php

namespace Jisan\LaravelReadyModular\Generator\Support;

class StubCompiler
{
    public static function compile(string $stub, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $stub = str_replace("__{$key}__", $value, $stub);
        }

        return $stub;
    }
}
