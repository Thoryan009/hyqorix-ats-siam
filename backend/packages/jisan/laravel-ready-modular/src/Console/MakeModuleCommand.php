<?php

namespace Jisan\LaravelReadyModular\Console;

use Illuminate\Console\Command;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\ModuleGenerator;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
        {name : Module name}
        {--type=crud : Module type (crud, auth, setting)}
        {--service=cache : Service type (plain, cache)}
        {--link= : Optional link to an existing module (e.g., User) for extra services like api , contracts, and extra services. }
        ';

    protected $description = 'Generate a ready-to-use Laravel module';

    public function __construct(
        private ModuleGenerator $generator,
        private Filesystem $fs
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $context = new ModuleContext(
            $this->argument('name'),
            $this->option('type') ?? 'crud',
            $this->option('service') ?? 'cache',
            $this->option('link')
        );

        if ($this->fs->exists($context->path)) {
            $this->error("❌ Module {$context->name} already exists.");
            return self::FAILURE;
        }

        // check if link module exists
        $linkModule = $this->option('link');

        if ($linkModule) {
            if (! $this->fs->exists(app_path("Modules/{$linkModule}"))) {
                $this->error("❌ Linked module {$linkModule} does not exist.");
                return self::FAILURE;
            }

            $context->markAsLinkModule();
        }
        $this->generator->generate($context);

        $this->info("✅ Module {$context->name} generated ({$context->moduleType}, {$context->serviceType})");

        return self::SUCCESS;
    }
}
