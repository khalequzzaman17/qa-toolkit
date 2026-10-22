<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class HttpSecurityHeadersCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Security headers (HSTS, X-Frame, CSP) configured'; }

    public function run()
    {
        $missing = [];

        // App-level: SetAsDefaultMiddleware-like checks via config.
        if (! config('session.cookie_secure_only') && ! config('session.secure')) {
            $missing[] = 'session.cookie_secure_only (cookies sent over HTTP)';
        }

        // Most projects add these via middleware like `SetSecurityHeaders` or
        // external packages. We scan bootstrap/app.php / Kernel.php for hints.
        $files = [
            base_path('bootstrap/app.php'),
            app_path('Http/Middleware/SecurityHeaders.php'),
            app_path('Http/Kernel.php'),
        ];

        $foundHsts = false;
        $foundCsp  = false;
        $foundFrame= false;

        foreach ($files as $file) {
            if (! file_exists($file)) {
                continue;
            }
            $contents = file_get_contents($file) ?: '';
            if (preg_match('/strict-transport-security/i', $contents)) $foundHsts = true;
            if (preg_match('/content-security-policy/i', $contents))   $foundCsp  = true;
            if (preg_match('/x-frame-options/i', $contents))           $foundFrame= true;
        }

        if (! $foundHsts)   $missing[] = 'Strict-Transport-Security header';
        if (! $foundCsp)   $missing[]  = 'Content-Security-Policy header';
        if (! $foundFrame) $missing[]  = 'X-Frame-Options header';

        if ($missing) {
            return $this->warning(
                'Missing security headers: ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('Security headers are configured.');
    }
}
