<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class RateLimitingRoutesCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'API routes apply RateLimiter (throttle:api)'; }

    public function run()
    {
        $routes = app('router')->getRoutes();
        $missing = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            if (! str_starts_with($uri, 'api/')) continue;

            $mws = $route->gatherMiddleware();
            $throttled = false;
            foreach ($mws as $m) {
                if (is_string($m) && str_starts_with($m, 'throttle')) {
                    $throttled = true; break;
                }
            }
            if (! $throttled) {
                $missing[] = $uri;
            }
        }

        $missing = array_unique($missing);

        if ($missing) {
            return $this->warning(
                count($missing) . ' API routes lack throttle middleware (samples: ' . implode(', ', array_slice($missing, 0, 5)) . ')',
                ['routes' => $missing]
            );
        }

        return $this->pass('All API routes apply throttle middleware.');
    }
}
