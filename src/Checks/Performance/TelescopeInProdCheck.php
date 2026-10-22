<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class TelescopeInProdCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Telescope is not enabled in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $providers = app('config')->get('app.providers') ?? [];
        $offenders = [];
        foreach ($providers as $provider) {
            if (str_contains($provider, 'TelescopeServiceProvider') || str_contains($provider, 'HorizonServiceProvider')) {
                $offenders[] = $provider;
            }
        }

        if ($offenders) {
            return $this->warning(
                'Performance/observability providers load in production: ' . implode(', ', $offenders),
                ['providers' => $offenders]
            );
        }

        return $this->pass('No Telescope/Horizon providers in production.');
    }
}
