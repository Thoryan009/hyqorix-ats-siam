<?php

namespace Jisan\LaravelReadyModular\Generator\ModuleTypes;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\GeneratorInterface;

class CrudModule implements GeneratorInterface
{
    public function __construct(private iterable $generators) {}

      public function getGenerators(): array
    {
        return $this->generators;
    }


    public function generate(ModuleContext $context): void
    {
        foreach ($this->generators as $generator) {
            $generator->generate($context);
        }
    }

      public function generateWithGenerators(
        $context,
        $service,
        array $generators
    ): void {
        foreach ($generators as $generator) {
            $generator->generate($context);
        }
    }
}
