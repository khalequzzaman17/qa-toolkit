<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ScheduledTasksCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Schedule has at least one task defined (if applicable)'; }

    public function run()
    {
        // Run `php artisan schedule:list` is overkill; look at routes/console.php or Console\Kernel.
        $files = [
            base_path('routes/console.php'),
            app_path('Console/Kernel.php'),
        ];

        $found = false;
        foreach ($files as $file) {
            if (file_exists($file)) {
                $contents = file_get_contents($file) ?: '';
                if (preg_match('/->(daily|hourly|everyFifteenMinutes|everyMinute|weekly|monthly|cron)\s*\(/', $contents)) {
                    $found = true; break;
                }
            }
        }

        // Also check schedule callbacks.
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $ref = new \ReflectionClass($schedule);
        $eventsProp = $ref->getProperty('events');
        $eventsProp->setAccessible(true);
        $events = $eventsProp->getValue($schedule);

        if (is_array($events) && count($events) > 0) {
            $found = true;
        }

        if (! $found) {
            return $this->warning('No scheduled tasks found — confirm none are needed before launch.');
        }

        return $this->pass('Schedule is configured.', ['count' => count($events)]);
    }
}
