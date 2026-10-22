<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class BackupFrequencyCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Backup cron is scheduled in Laravel scheduler'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        // Check whether spatie/laravel-backup is scheduled.
        $files = [
            base_path('routes/console.php'),
            app_path('Console/Kernel.php'),
        ];

        $found = false;
        foreach ($files as $file) {
            if (! file_exists($file)) continue;
            $contents = file_get_contents($file) ?: '';
            if (str_contains($contents, 'backup:run') || str_contains($contents, 'backup:clean') || str_contains($contents, 'backup:monitor')) {
                $found = true; break;
            }
        }

        if (! $found) {
            return $this->warning('No backup:* command scheduled — add `->daily()->at(02:00)` for `backup:run`.');
        }

        return $this->pass('Backup commands are scheduled.');
    }
}
