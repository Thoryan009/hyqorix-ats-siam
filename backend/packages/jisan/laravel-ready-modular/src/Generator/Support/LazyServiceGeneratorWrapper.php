<?php

namespace Jisan\LaravelReadyModular\Generator\Support;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\GeneratorInterface;
use Jisan\LaravelReadyModular\Generator\Contracts\SkippableGenerator;
use Jisan\LaravelReadyModular\Generator\ServiceTypes\ServiceGeneratorContract;

class LazyServiceGeneratorWrapper implements GeneratorInterface
{
    private $instance = null;

    public function __construct(private \Closure $factory) {}

    public function generate(ModuleContext $context, ?ServiceGeneratorContract $serviceStrategy = null): void
    {
        if ($this->instance === null) {
            $this->instance = ($this->factory)();
        }

        $this->instance->generate($context, $serviceStrategy);
    }

     public function key(): ?string
    {
         return null;
    }
}
