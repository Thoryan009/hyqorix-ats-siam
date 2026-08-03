<?php

namespace Jisan\LaravelReadyModular\Generator\Contracts;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;

interface ModuleTypeInterface
{
    public function generate(ModuleContext $context): void;
}
