<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class CsrfProtectionCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'VerifyCsrfToken middleware active on web routes'; }

    public function run()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);

        // Inspect the middleware stack for the VerifyCsrfToken contract.
        $reflection = new \ReflectionClass($kernel);
        $property = $reflection->getProperty('middlewareGroups');
        $property->setAccessible(true);
        $groups = $property->getValue($kernel);

        $webGroup   = $groups['web'] ?? [];
        $hasVerify  = false;

        foreach ($webGroup as $middleware) {
            if (is_string($middleware) && (
                str_contains($middleware, 'VerifyCsrfToken')
                || is_subclass_of($middleware, \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            )) {
                $hasVerify = true;
                break;
            }
        }

        if (! $hasVerify) {
            return $this->critical('VerifyCsrfToken is missing from the "web" middleware group.');
        }

        $exempt = (array) config('qa-toolkit.ignore.csrf_exempt_routes', []);

        if ($exempt) {
            return $this->warning(
                'CSRF protection is active, but the following routes are exempt: ' . implode(', ', $exempt),
                ['exempt' => $exempt]
            );
        }

        return $this->pass('VerifyCsrfToken is registered in the web middleware group.');
    }
}
