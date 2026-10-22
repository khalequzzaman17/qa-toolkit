<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class QueryLogDisabledCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'DB query logging is off in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        // Laravel does not log queries by default in production. Check for DB::listen / DB::enableQueryLog in app.
        $fs = new \Illuminate\Filesystem\Filesystem();
        $app = app_path();
        $offenders = [];

        if (is_dir($app)) {
            foreach ($fs->allFiles($app) as $file) {
                $contents = file_get_contents($file->getRealPath()) ?: '';
                if (preg_match('/DB::enableQueryLog|DB::listen\(/', $contents)) {
                    $offenders[] = $file->getRelativePathname();
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'DB query logging hooks detected in app/: ' . implode(', ', $offenders),
                ['files' => $offenders]
            );
        }

        return $this->pass('No DB query logging in app/.');
    }
}
