<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class RouteModelBindingCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Implicit {model} bindings use scope or auth gate'; }

    public function run()
    {
        // Flag any route that uses {user} or {post} (implicit binding) but
        // has neither an authorization middleware nor a scoped route param.
        $explicit = [];
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (! preg_match('/\{(user|post|order|invoice|account)\}/', $uri, $m)) {
                continue;
            }
            $implicit = ! str_ends_with($m[1], 's') && ! str_contains($m[1], ':');
            $hasAuthGate = false;
            foreach ($route->gatherMiddleware() as $mw) {
                if (is_string($mw) && (str_starts_with($mw, 'can:') || str_starts_with($mw, 'auth'))) {
                    $hasAuthGate = true; break;
                }
            }
            if ($implicit && ! $hasAuthGate) {
                $explicit[] = $route->uri();
            }
        }

        if ($explicit) {
            return $this->warning(
                'Implicit route-model bindings without auth/can middleware: ' . implode(', ', array_slice($explicit, 0, 10)),
                ['routes' => $explicit]
            );
        }

        return $this->pass('Route-model bindings are protected.');
    }
}
