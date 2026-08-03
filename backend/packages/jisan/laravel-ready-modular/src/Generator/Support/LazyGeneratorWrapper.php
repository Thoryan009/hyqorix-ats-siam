<?php

namespace Jisan\LaravelReadyModular\Generator\Support;

use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\SkippableGenerator;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;

class LazyGeneratorWrapper
{
    private $instance = null;

    public function __construct(private \Closure $factory) {}

    /**
     * Resolve the actual generator and call generate()
     */

    protected function resolve()
    {
        return $this->instance ??= ($this->factory)();
    }

    // public function generate($context): void
    // {
    //     if ($this->instance === null) {
    //         $this->instance = ($this->factory)();
    //     }

    //     $this->instance->generate($context);
    // }

    public function generate(ModuleContext $context): void
    {
        $generator = $this->resolve();

        if (
            $generator instanceof ConditionalGenerator &&
            ! $generator->shouldRun($context)
        ) {
            return; // 🔥 skip cleanly
        }

        $generator->generate($context);
    }

    public function key(): ?string
    {
        $generator = $this->resolve();

        return $generator instanceof ConditionalGenerator
            ? $generator->key()
            : null;
    }
}
