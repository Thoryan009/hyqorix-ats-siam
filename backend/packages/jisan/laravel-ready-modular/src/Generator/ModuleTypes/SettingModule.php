<?php

namespace Jisan\LaravelReadyModular\Generator\ModuleTypes;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\GeneratorInterface;

class SettingModule implements GeneratorInterface
{
    public function __construct(private iterable $generators) {}

    public function generate(ModuleContext $context): void
    {
        foreach ($this->generators as $generator) {
            $generator->generate($context);
        }
    }
}
