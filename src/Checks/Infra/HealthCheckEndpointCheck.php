<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class HealthCheckEndpointCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'A /up or /health endpoint exists for load balancer checks'; }

    public function run()
    {
        foreach (app('router')->getRoutes() as $route) {
            $uri = $route->uri();
            if (in_array($uri, ['up', 'health', 'healthz', 'ping', 'status'], true)) {
                return $this->pass("Health endpoint at /{$uri}.");
            }
        }

        // Laravel 11+ ships /up automatically.
        if (file_exists(base_path('bootstrap/app.php'))) {
            $bs = file_get_contents(base_path('bootstrap/app.php')) ?: '';
            if (str_contains($bs, 'withHealthChecks') || str_contains($bs, 'health')) {
                return $this->pass('Health-checks middleware detected in bootstrap/app.php.');
            }
        }

        return $this->warning('No /up or /health endpoint detected — add one for load balancer liveness checks.');
    }
}
