<?php

namespace Jisan\LaravelReadyModular\Generator;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\ServiceTypes\ServiceGeneratorContract;

class ModuleGenerator
{
    public function __construct(
        private array $modules,
        private array $services
    ) {}

    public function generate(ModuleContext $context): void
    {

        // Resolve the service strategy
        $serviceStrategy = $this->services[$context->serviceType];


        // First, generate the service file
        $serviceStrategy->generate($context);

        // Then generate module files & controller using this service
        $this->modules[$context->moduleType]->generate($context, $serviceStrategy);
    }

    public function getServiceStrategy(string $type): ServiceGeneratorContract
    {
        return $this->services[$type];
    }

}
