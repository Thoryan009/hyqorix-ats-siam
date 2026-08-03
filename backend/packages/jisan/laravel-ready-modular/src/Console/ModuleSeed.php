<?php

namespace Jisan\LaravelReadyModular\Console;

use Illuminate\Console\Command;

class ModuleSeed extends Command
{
    protected $signature = 'module:seed {module}';
    protected $description = 'Run seeder for a specific module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $seederClass = "App\\Modules\\{$module}\\Seeders\\{$module}Seeder";
        $seederPath = app_path("Modules/{$module}/Seeders/{$module}Seeder.php");

        if (! file_exists($seederPath)) {
            $this->error("❌ Seeder not found for module: {$module}");
            return self::FAILURE;
        }

        $this->call('db:seed', [
            '--class' => $seederClass,
        ]);

        $this->info("✅ Seeder executed for module: {$module}");

        return self::SUCCESS;
    }
}

