<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class ThrottlingMiddlewareCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Login/risky POST routes apply throttle middleware'; }

    public function run()
    {
        $risky = ['login', 'register', 'password/email', 'password/reset', 'forgot-password'];
        $missing = [];

        foreach (Route::getRoutes() as $route) {
            $uri = ltrim($route->uri(), '/');

            foreach ($risky as $pattern) {
                if (! fnmatch($pattern, $uri) && $uri !== $pattern) {
                    continue;
                }

                $middlewares = $route->gatherMiddleware();
                $throttled = false;
                foreach ($middlewares as $m) {
                    if (is_string($m) && str_starts_with($m, 'throttle')) {
                        $throttled = true;
                        break;
                    }
                }

                if (! $throttled) {
                    $missing[] = $uri;
                }
            }
        }

        $missing = array_unique($missing);

        if ($missing) {
            return $this->warning(
                'Routes typically targeted by brute force are missing throttle middleware: ' . implode(', ', $missing),
                ['routes' => $missing]
            );
        }

        return $this->pass('Sensitive routes apply throttle middleware.');
    }
}
