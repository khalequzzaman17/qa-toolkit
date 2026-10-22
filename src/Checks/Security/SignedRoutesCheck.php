<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class SignedRoutesCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Public action routes use signed middleware'; }

    public function run()
    {
        $patterns = ['/unsubscribe/', '/accept-invite/', '/cancel/', '/verify-email/', '/reset-password/'];

        $routes = app('router')->getRoutes();
        $missing = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            foreach ($patterns as $p) {
                if (preg_match($p, $uri)) {
                    $mws = $route->gatherMiddleware();
                    $signed = false;
                    foreach ($mws as $m) {
                        if (is_string($m) && str_contains($m, 'signed')) {
                            $signed = true; break;
                        }
                    }
                    if (! $signed) {
                        $missing[] = $uri;
                    }
                }
            }
        }

        $missing = array_unique($missing);

        if ($missing) {
            return $this->warning(
                'Public action routes lack signed middleware: ' . implode(', ', $missing),
                ['routes' => $missing]
            );
        }

        return $this->pass('Public action routes use signed URLs.');
    }
}
