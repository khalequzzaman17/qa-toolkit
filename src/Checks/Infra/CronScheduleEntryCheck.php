<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class CronScheduleEntryCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'system crontab includes `* * * * * cd ... schedule:run`'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        // Check the user's crontab (assumes the worker is the same UID).
        $crontab = (string) shell_exec('crontab -l 2>/dev/null');

        if ($crontab === '') {
            return $this->warning('No crontab found for current user — Laravel scheduler needs * * * * * entry.');
        }

        if (! str_contains($crontab, 'schedule:run')) {
            return $this->warning(
                "Crontab does not contain `php artisan schedule:run`. Add: * * * * * cd " . base_path() . " && php artisan schedule:run >> /dev/null 2>&1"
            );
        }

        return $this->pass('schedule:run is present in crontab.');
    }
}
