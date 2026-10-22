<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class DebugBarCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Debug bar / Telescope disabled in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $providers = array_keys(app()->getLoadedProviders());
        $offenders = [];

        foreach ($providers as $provider) {
            if (str_contains($provider, 'DebugbarServiceProvider')
                || str_contains($provider, 'TelescopeServiceProvider')
                || str_contains($provider, 'ClockworkServiceProvider')
                || str_contains($provider, 'HorizonServiceProvider')
            ) {
                $offenders[] = $provider;
            }
        }

        if ($offenders) {
            return $this->critical(
                'Debug/introspection providers are enabled in production: ' . implode(', ', $offenders),
                ['providers' => $offenders]
            );
        }

        return $this->pass('No debug/introspection providers loaded in production.');
    }
}
