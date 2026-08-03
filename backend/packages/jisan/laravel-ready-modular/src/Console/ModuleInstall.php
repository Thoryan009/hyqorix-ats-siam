<?php

namespace Jisan\LaravelReadyModular\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleInstall extends Command
{
    protected $signature = 'module:install {module}';
    protected $description = 'Install a module (migrate + seed)';

    public function handle(): int
    {
        $module = $this->argument('module');
        $modulePath = app_path("Modules/{$module}");

        if (!File::exists($modulePath)) {
            $this->error("❌ Module {$module} does not exist.");
            return self::FAILURE;
        }

        $this->info("📦 Installing module: {$module}");

        // Run module migrations safely
        $this->call('migrate', [
            '--path' => "app/Modules/{$module}/Migrations",
        ]);
        $this->info("✔ Migrations completed");

        // Run module seeder safely
        $this->call('module:seed', [
            'module' => $module,
        ]);
        $this->info("✔ Seeder completed");

        $this->info("✅ Module {$module} installed successfully");

        return self::SUCCESS;
    }
}

