<?php

namespace Jisan\LaravelReadyModular\Console;

use Illuminate\Console\Command;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\ModuleGenerator;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;

class MakeSubEntityCommand extends Command
{
    protected $signature = 'make:sub-entity
        {module : Parent module name}
        {entity : Entity name (e.g., Country)}
        {--service=cache : Service type (plain, cache)}
        {--short : Exclude (Model, Migration, Seeder) files}
        ';

    protected $description = 'Generate a sub-entity inside an existing module';

    public function __construct(
        private ModuleGenerator $generator,
        private Filesystem $fs
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $module = $this->argument('module');
        $entity = $this->argument('entity');
        $service = $this->option('service') ?? 'plain';
        $isShort = $this->option('short') ?? false;

        $context = new ModuleContext($entity, 'crud', $service, $module);
        $context->path = base_path("app/Modules/{$module}"); // keep inside parent module

        $context->markAsSubEntity();
        if ($isShort) {
            $context->markAsShortEntity();
        }

        // Ensure module exists
        if (! $this->fs->exists($context->path)) {
            $this->error("❌ Module {$module} does not exist.");
            return self::FAILURE;
        }

        // Prevent duplicate sub-entity for the same module
        $modelPath = "{$context->path}/Models/{$entity}.php";
        if ($this->fs->exists($modelPath)) {
            $this->error("❌ Sub-entity {$entity} already exists inside module {$module}.");
            return self::FAILURE;
        }

        // Generate all files EXCEPT api.php
        $this->generator->generate($context);

        $this->info("✅ Sub-entity {$entity} generated inside module {$module}");

        return self::SUCCESS;
    }


}
