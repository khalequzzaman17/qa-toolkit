<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class NamedRoutesCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'All routes are named (route() helper won\'t fail)'; }

    public function run()
    {
        $unnamed = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if ($name === null || $name === '') {
                $unnamed[] = $route->uri() . ' [' . implode(',', $route->methods()) . ']';
            }
        }

        $unnamed = array_slice($unnamed, 0, 15);

        if ($unnamed) {
            return $this->warning(
                count($unnamed) . ' routes are unnamed (samples: ' . implode(', ', $unnamed) . ')',
                ['routes' => $unnamed]
            );
        }

        return $this->pass('All routes have names.');
    }
}
