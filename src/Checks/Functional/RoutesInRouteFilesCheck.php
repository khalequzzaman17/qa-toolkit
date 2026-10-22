<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class RoutesInRouteFilesCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'routes/web.php and routes/api.php are loaded'; }

    public function run()
    {
        $files = [
            base_path('routes/web.php'),
            base_path('routes/api.php'),
        ];

        $missing = [];
        foreach ($files as $file) {
            if (! file_exists($file)) {
                $missing[] = basename($file);
            }
        }

        if ($missing) {
            return $this->warning('Missing route files: ' . implode(', ', $missing), ['missing' => $missing]);
        }

        // Verify they're referenced in bootstrap/app.php or RouteServiceProvider.
        $bs = base_path('bootstrap/app.php');
        $rp = app_path('Providers/RouteServiceProvider.php');
        $loaded = false;

        foreach ([$bs, $rp] as $file) {
            if (! file_exists($file)) continue;
            $contents = file_get_contents($file) ?: '';
            if (str_contains($contents, 'routes/web.php') || str_contains($contents, 'routes/api.php')) {
                $loaded = true; break;
            }
        }

        if (! $loaded) {
            return $this->warning('Route files exist but are not explicitly loaded by the bootstrap or RouteServiceProvider.');
        }

        return $this->pass('Both web.php and api.php exist and are wired up.');
    }
}
