<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use QaToolkit\Support\Cfg;
use Illuminate\Filesystem\Filesystem;

class MassAssignmentCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No models use guarded=[] (mass assignment wide open)'; }

    public function run()
    {
        $allowed = Cfg::ignore('fillable_wildcard_models');
        $fs      = new Filesystem();
        $modelPath = app_path('Models');

        if (! is_dir($modelPath)) {
            return $this->pass('No app/Models directory; nothing to scan.');
        }

        $offenders = [];

        foreach ($fs->allFiles($modelPath) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';

            if (preg_match('/\bguarded\s*=\s*\[\s*\]/', $contents)) {
                $class = 'App\\Models\\' . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $file->getRelativePathname()
                );

                if (in_array($class, $allowed, true)) {
                    continue;
                }

                $offenders[] = $class;
            }
        }

        if ($offenders) {
            return $this->error(
                'Models using $guarded = [] are vulnerable to mass assignment: ' . implode(', ', $offenders),
                ['offenders' => $offenders]
            );
        }

        return $this->pass('No models use $guarded = [].');
    }
}
