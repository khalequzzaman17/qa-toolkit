<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class BackupConfiguredCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Database backup strategy configured'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $composer = base_path('composer.json');
        $hasBackup = false;
        if (file_exists($composer)) {
            $required = json_decode((string) file_get_contents($composer), true)['require'] ?? [];
            if (isset($required['spatie/laravel-backup'])) {
                $hasBackup = true;
            }
        }

        if (! $hasBackup) {
            return $this->warning(
                'No backup package detected — install spatie/laravel-backup or wire up mysqldump cron.'
            );
        }

        $config = config('backup');
        if (! is_array($config)) {
            return $this->warning('spatie/laravel-backup installed but config not published.');
        }

        $dest = $config['backup']['destination']['disks'] ?? [];
        if (empty($dest)) {
            return $this->warning('Backup config has no destination disks.');
        }

        return $this->pass('Backup package + destination disks are configured.', ['disks' => $dest]);
    }
}
