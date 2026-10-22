<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class SentryOrBugsnagCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Error monitoring SDK is configured (Sentry/Bugsnag)'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $composer = base_path('composer.json');
        if (file_exists($composer)) {
            $reqs = array_keys(json_decode((string) file_get_contents($composer), true)['require'] ?? []);
            foreach ($reqs as $pkg) {
                if (in_array($pkg, ['sentry/sentry-laravel', 'bugsnag/bugsnag-laravel', 'rollbar/rollbar-laravel'], true)) {
                    $dsn = env('SENTRY_LARAVEL_DSN') ?: env('SENTRY_DSN') ?: env('BUGSNAG_API_KEY') ?: env('ROLLBAR_ACCESS_TOKEN');
                    if (! $dsn) {
                        return $this->warning("{$pkg} installed but DSN env var is missing.");
                    }
                    return $this->pass("Error monitoring via {$pkg}.");
                }
            }
        }

        return $this->warning('No error monitoring SDK detected in production.');
    }
}
