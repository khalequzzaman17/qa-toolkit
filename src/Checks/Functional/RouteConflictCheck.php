<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class RouteConflictCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'No two routes register the same verb+URI'; }

    public function run()
    {
        $seen = [];
        $conflicts = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            $methods = array_diff($route->methods(), ['HEAD']);
            foreach ($methods as $m) {
                $key = $m . ' ' . $uri;
                if (isset($seen[$key])) {
                    $conflicts[] = $key;
                }
                $seen[$key] = true;
            }
        }

        if ($conflicts) {
            return $this->error(
                'Routes registered twice with the same verb+URI: ' . implode(', ', array_slice($conflicts, 0, 5)),
                ['conflicts' => $conflicts]
            );
        }

        return $this->pass('No route conflicts.');
    }
}
