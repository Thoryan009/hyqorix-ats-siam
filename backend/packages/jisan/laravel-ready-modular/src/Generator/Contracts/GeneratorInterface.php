<?php

namespace Jisan\LaravelReadyModular\Generator\Contracts;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;

interface GeneratorInterface
{
    /**
     * @param ModuleContext $context
     *
     */
    public function generate(ModuleContext $context): void;
}
