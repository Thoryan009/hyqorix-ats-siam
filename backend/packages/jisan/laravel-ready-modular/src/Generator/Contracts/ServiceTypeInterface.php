<?php

namespace Jisan\LaravelReadyModular\Generator\Contracts;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;

interface ServiceTypeInterface
{
    public function generate(ModuleContext $context): void;
}
