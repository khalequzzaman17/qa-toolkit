<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class DebugModeCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'APP_DEBUG is false in production'; }

    public function run()
    {
        $env   = (string) config('app.env');
        $debug = config('app.debug');

        if ($env === 'production' && $debug) {
            return $this->critical(
                'APP_DEBUG=true in production. Stack traces and env vars will be exposed to users.'
            );
        }

        if (in_array($env, ['local', 'testing', 'development'], true) && $debug) {
            return $this->pass("APP_DEBUG=true (allowed in {$env}).");
        }

        if (! $debug) {
            return $this->pass('APP_DEBUG=false.');
        }

        return $this->warning('APP_DEBUG is enabled in an environment that is not production, but is also not local/testing.');
    }
}
