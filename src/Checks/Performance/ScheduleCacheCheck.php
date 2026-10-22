<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class ScheduleCacheCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'schedule:cache present (cache cron definitions)'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        if (file_exists(base_path('bootstrap/cache/schedule-cache.php'))) {
            return $this->pass('schedule cache file present.');
        }

        return $this->warning(
            'Schedule cache missing — run `php artisan schedule:cache` for ~10x faster cron bootstrap.'
        );
    }
}
