<?php

namespace Jisan\LaravelReadyModular\Generator\Contracts;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;

interface ConditionalGenerator
{
    /**
     * Unique identifier for merging / filtering
     */
    public function key(): string;

    /**
     * Decide whether this generator should run
     */
    public function shouldRun(ModuleContext $context): bool;
}
