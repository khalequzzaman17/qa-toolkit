<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class RouteCacheConfiguredCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Route cache + config cache present in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $issues = [];

        if (! file_exists(base_path('bootstrap/cache/routes-v7.php'))
            && ! file_exists(base_path('bootstrap/cache/routes.php'))) {
            $issues[] = 'route cache missing (run `php artisan route:cache`)';
        }

        if (! file_exists(base_path('bootstrap/cache/config.php'))) {
            $issues[] = 'config cache missing (run `php artisan config:cache`)';
        }

        if (! file_exists(base_path('bootstrap/cache/events.php'))) {
            $issues[] = 'event cache missing (run `php artisan event:cache`)';
        }

        if (! file_exists(base_path('bootstrap/cache/views.php'))) {
            $issues[] = 'view cache missing (run `php artisan view:cache`)';
        }

        if ($issues) {
            return $this->warning(
                'Framework caches are missing in production: ' . implode(' | ', $issues),
                ['issues' => $issues]
            );
        }

        return $this->pass('All framework caches present.');
    }
}
