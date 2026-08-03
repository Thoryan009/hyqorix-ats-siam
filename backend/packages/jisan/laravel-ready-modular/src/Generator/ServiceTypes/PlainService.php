<?php

namespace Jisan\LaravelReadyModular\Generator\ServiceTypes;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Generators\Services\PlainServiceGenerator;
use Jisan\LaravelReadyModular\Generator\ServiceTypes\ServiceGeneratorContract;

class PlainService implements ServiceGeneratorContract
{
    public function __construct(
        private PlainServiceGenerator $generator
    ) {}

    /**
     * Generate the PlainService class file
     */
    public function generate(ModuleContext $context): void
    {
        $this->generator->generate($context);
    }

    /**
     * Return the index method call for the controller
     */
    public function indexMethodCall(ModuleContext $context): string
    {
        return '$this->service->paginate($per_page)';
    }

    /**
     * Return the show method call for the controller
     */
    public function showMethodCall(ModuleContext $context): string
    {
        return '$this->service->find($id)';
    }
}
