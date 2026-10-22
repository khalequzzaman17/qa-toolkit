<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class ClosureRoutesCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No closure-based routes (covered by route:cache)'; }

    public function run()
    {
        $closures = [];
        foreach (Route::getRoutes() as $route) {
            $action = $route->getAction('uses');
            if ($action instanceof \Closure) {
                $closures[] = $route->uri();
            }
        }

        if ($closures) {
            return $this->warning(
                'Closure routes prevent `route:cache`: ' . implode(', ', array_slice($closures, 0, 5)) . (count($closures) > 5 ? ' …' : ''),
                ['closures' => $closures]
            );
        }

        return $this->pass('All routes use controller actions; route:cache-safe.');
    }
}
