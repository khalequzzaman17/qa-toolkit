<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class TrustedProxiesCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'TrustProxies middleware configured for the load balancer'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $middleware = app_path('Http/Middleware/TrustProxies.php');
        if (! file_exists($middleware)) {
            // Laravel 11+ uses TrustProxies from the framework default; check config.
            $trusted = config('trustedproxy.proxies');
            if ($trusted === null || $trusted === '') {
                return $this->warning('No TrustProxies middleware or trustedproxy config — X-Forwarded-* will be ignored.');
            }
            return $this->pass('trustedproxy.proxies is configured.', ['proxies' => $trusted]);
        }

        $contents = file_get_contents($middleware) ?: '';
        if (preg_match('/\$proxies\s*=\s*[\'"]\*[\'"]/', $contents)) {
            return $this->warning(
                'TrustProxies uses wildcard "*" — restrict to known LB IP ranges.'
            );
        }

        if (preg_match('/\$proxys?\s*=\s*[\'"][^\'"]+[\'"]/', $contents) || preg_match('/protected\s+\$proxies\s*=\s*[\'"][^\'"]+[\'"]/', $contents)) {
            return $this->pass('TrustProxies middleware restricts proxies.');
        }

        return $this->warning('TrustProxies middleware present but proxies property is empty.');
    }
}
