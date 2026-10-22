<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class EmptyRoutesCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Application has at least one route registered'; }

    public function run()
    {
        $routes = Route::getRoutes();
        $count  = count($routes);

        if ($count === 0) {
            return $this->error('No routes registered — verify routes/web.php is being loaded.');
        }

        if ($count < 5) {
            return $this->warning("Only {$count} routes registered — expected more for a real application.");
        }

        return $this->pass("{$count} routes registered.", ['count' => $count]);
    }
}
