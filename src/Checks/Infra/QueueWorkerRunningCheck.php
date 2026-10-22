<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class QueueWorkerRunningCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'queue:work processes are running (production)'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        // Use ps + grep — heuristics.
        $ps = (string) shell_exec('ps aux 2>/dev/null | grep "queue:work" | grep -v grep');
        $count = substr_count(trim($ps), "\n") + ($ps ? 1 : 0);

        if (! $ps || $count === 0) {
            return $this->warning(
                'No `php artisan queue:work` processes running. Run via supervisor: php artisan queue:work redis --tries=3 --backoff=60'
            );
        }

        return $this->pass("{$count} queue worker(s) running.", ['count' => $count]);
    }
}
