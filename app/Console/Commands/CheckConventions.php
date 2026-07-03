<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckConventions extends Command
{
    protected $signature = 'conventions:check';

    protected $description = 'Check that all modules follow LaravelAdmin conventions';

    /**
     * Modules that are intentionally static (no service/interface required).
     * Components is purely a UI showcase with no business logic.
     */
    private array $staticModules = ['Components'];

    public function handle(): int
    {
        $modulesPath = base_path('Modules');

        if (! File::isDirectory($modulesPath)) {
            $this->error('Modules directory not found: '.$modulesPath);

            return self::FAILURE;
        }

        $modules = File::directories($modulesPath);
        $failed = false;

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $isStatic = in_array($moduleName, $this->staticModules);

            // 1. Each module must have a ServiceProvider in its own Providers dir
            $providerDir = $modulePath.'/app/Providers';
            if (! File::isDirectory($providerDir) || empty(File::glob($providerDir.'/*ServiceProvider.php'))) {
                $this->error("[{$moduleName}] Missing ServiceProvider in app/Providers/");
                $failed = true;
            }

            // 2. Each module must have at least one route file
            $routesDir = $modulePath.'/routes';
            if (! File::isDirectory($routesDir) || empty(File::glob($routesDir.'/*.php'))) {
                $this->error("[{$moduleName}] Missing route file in routes/");
                $failed = true;
            }

            // 3. Non-static modules must have at least one Interface file
            if (! $isStatic) {
                $interfacesDir = $modulePath.'/app/Interfaces';
                if (! File::isDirectory($interfacesDir) || empty(File::glob($interfacesDir.'/I*.php'))) {
                    $this->error("[{$moduleName}] Missing Interface file in app/Interfaces/ (I*.php)");
                    $failed = true;
                }
            }

            // 4. Each module must have at least one test file
            $testsDir = $modulePath.'/tests';
            $testFiles = File::glob($testsDir.'/**/*.php');
            if (empty($testFiles)) {
                $testFiles = File::glob($testsDir.'/*.php');
            }
            if (! File::isDirectory($testsDir) || empty($testFiles)) {
                $this->error("[{$moduleName}] Missing test file in tests/");
                $failed = true;
            }

            // 5. module.json must list a provider
            $moduleJson = $modulePath.'/module.json';
            if (! File::exists($moduleJson)) {
                $this->error("[{$moduleName}] Missing module.json");
                $failed = true;
            } else {
                $json = json_decode(File::get($moduleJson), true);
                if (empty($json['providers'])) {
                    $this->error("[{$moduleName}] module.json does not list any provider");
                    $failed = true;
                }
            }
        }

        if ($failed) {
            return self::FAILURE;
        }

        $this->info('All conventions passed!');

        return self::SUCCESS;
    }
}
