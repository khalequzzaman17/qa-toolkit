<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class SeedersExistCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Database has at least one seeder + DatabaseSeeder wires it'; }

    public function run()
    {
        $dir = base_path('database/seeders');
        if (! is_dir($dir)) {
            $dir = base_path('database/seeds');
            if (! is_dir($dir)) {
                return $this->warning('No database/seeders directory — set up at least DatabaseSeeder.');
            }
        }

        $files = glob($dir . '/*.php');
        if (empty($files)) {
            return $this->warning('No seeder classes found in ' . $dir);
        }

        // Verify DatabaseSeeder references at least one other seeder.
        $dbSeeder = $dir . '/DatabaseSeeder.php';
        if (! file_exists($dbSeeder)) {
            return $this->warning('DatabaseSeeder.php is missing.');
        }

        $contents = file_get_contents($dbSeeder) ?: '';
        if (! preg_match('/\$this->call\s*\(/', $contents)) {
            return $this->warning('DatabaseSeeder has no $this->call(...) — nothing will be seeded.');
        }

        return $this->pass('Seeders are present and wired into DatabaseSeeder.');
    }
}
