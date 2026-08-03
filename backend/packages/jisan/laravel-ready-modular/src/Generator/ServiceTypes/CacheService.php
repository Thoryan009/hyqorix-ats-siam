<?php

namespace Jisan\LaravelReadyModular\Generator\ServiceTypes;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Generators\Services\CacheServiceGenerator;
use Jisan\LaravelReadyModular\Generator\ServiceTypes\ServiceGeneratorContract;

class CacheService implements ServiceGeneratorContract
{
    public function __construct(
        private CacheServiceGenerator $generator
    ) {}

    /**
     * Generate the CacheService class file
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
        return '$this->service->getPaginatedDataWithCache($page, $per_page)';
    }

    /**
     * Return the show method call for the controller
     */
    public function showMethodCall(ModuleContext $context): string
    {
        return '$this->service->getById($id)';
    }
}
