<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use QaToolkit\Support\Cfg;
use Illuminate\Support\Facades\Route;

class AuthMiddlewareCoverageCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Non-public GET routes apply auth or auth:api'; }

    public function run()
    {
        $allowlist = Cfg::ignore('unauthenticated_routes_allowlist');

        $orphan = [];

        foreach (Route::getRoutes() as $route) {
            $methods = array_diff($route->methods(), ['HEAD']);
            if (! in_array('GET', $methods, true) && ! in_array('ANY', $methods, true)) {
                continue;
            }

            $uri = '/' . ltrim($route->uri(), '/');

            if ($this->matchesAllowlist($uri, $allowlist)) {
                continue;
            }

            $middlewares = array_map(
                fn ($m) => is_string($m) ? $m : (is_array($m) ? ($m[0] ?? '') : ''),
                $route->gatherMiddleware()
            );

            $hasAuth = false;
            foreach ($middlewares as $m) {
                if (is_string($m) && (str_starts_with($m, 'auth') || str_starts_with($m, 'signed'))) {
                    $hasAuth = true;
                    break;
                }
            }

            if (! $hasAuth) {
                $orphan[] = $uri;
            }
        }

        $orphan = array_unique($orphan);

        if (count($orphan) > 25) {
            return $this->warning(
                count($orphan) . ' GET routes are publicly accessible — review for missing auth.',
                ['samples' => array_slice($orphan, 0, 25)]
            );
        }

        if ($orphan) {
            return $this->warning(
                'GET routes without auth middleware: ' . implode(', ', $orphan),
                ['routes' => $orphan]
            );
        }

        return $this->pass('All non-allowlisted GET routes are protected by auth.');
    }

    private function matchesAllowlist(string $uri, array $allowlist): bool
    {
        foreach ($allowlist as $pattern) {
            $pattern = '/' . ltrim($pattern, '/');
            if (fnmatch($pattern, $uri) || $uri === $pattern) {
                return true;
            }
        }
        return false;
    }
}
