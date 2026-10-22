<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class FallbackRouteCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'A fallback 404 route is defined'; }

    public function run()
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getAction('uses');
            if (is_string($action) && (str_contains($action, 'fallback') || str_contains($action, 'NotFound'))) {
                return $this->pass('A fallback route is registered.');
            }
            if ($action instanceof \Closure) {
                $reflection = new \ReflectionFunction($action);
                if (str_starts_with($reflection->getName() ?? '', 'fallback') || $route->getActionName() === 'Closure') {
                    if (str_contains($route->uri(), '{fallback}')) {
                        return $this->pass('A fallback route is registered.');
                    }
                }
            }
        }

        // The simple check — fallbackRoute is registered.
        if (Route::has('fallback')) {
            return $this->pass('Fallback route named "fallback" is registered.');
        }

        return $this->warning('No fallback route registered — define `Route::fallback(...)` for clean 404s.');
    }
}
