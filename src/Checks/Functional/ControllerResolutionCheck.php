<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class ControllerResolutionCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'All routes resolve to an existing controller method'; }

    public function run()
    {
        $broken = [];

        foreach (Route::getRoutes() as $route) {
            $uses = $route->getAction('uses');

            if (! is_string($uses) || ! str_contains($uses, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $uses, 2);

            if (! class_exists($class)) {
                $broken[] = "{$route->uri()} → missing class {$class}";
                continue;
            }

            if (! method_exists($class, $method)) {
                $broken[] = "{$route->uri()} → missing method {$class}::{$method}";
            }
        }

        $broken = array_slice($broken, 0, 15);

        if ($broken) {
            return $this->error(
                'Routes reference missing controllers/methods: ' . implode(' | ', $broken),
                ['broken' => $broken]
            );
        }

        return $this->pass('All routes resolve to existing controller methods.');
    }
}
