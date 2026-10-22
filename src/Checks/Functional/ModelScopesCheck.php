<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ModelScopesCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Active models expose query scopes (active/published)'; }

    public function run()
    {
        $path = app_path('Models');
        if (! is_dir($path)) {
            return $this->pass('No app/Models directory.');
        }

        $fs = new Filesystem();
        $missing = [];

        // Soft-delete / state-column models should have a scope() method.
        foreach ($fs->allFiles($path) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            $class = 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (! class_exists($class)) continue;

            $reflection = new \ReflectionClass($class);
            if (! $reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) continue;

            // If model uses SoftDeletes, expects a scope to filter trashed.
            if (preg_match('/use\s+SoftDeletes;/', $contents)) {
                $hasScope = preg_match('/function\s+scope[A-Z]/', $contents);
                if (! $hasScope) {
                    $missing[] = $class . ' (uses SoftDeletes, no scopes)';
                }
            }
        }

        if ($missing) {
            return $this->warning(
                'Models with SoftDeletes but no scopes: ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('Soft-deletable models have scopes.');
    }
}
