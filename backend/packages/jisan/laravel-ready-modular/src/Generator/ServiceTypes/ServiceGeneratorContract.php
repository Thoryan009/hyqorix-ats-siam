<?php

namespace Jisan\LaravelReadyModular\Generator\ServiceTypes;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;

interface ServiceGeneratorContract
{
    public function generate(ModuleContext $context): void;

    public function indexMethodCall(ModuleContext $context): string;

    public function showMethodCall(ModuleContext $context): string;
}
