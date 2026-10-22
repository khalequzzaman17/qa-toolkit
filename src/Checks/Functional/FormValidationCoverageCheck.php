<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Validator;

class FormValidationCoverageCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'POST/PUT/DELETE routes apply validate() or FormRequest'; }

    public function run()
    {
        $fs = new \Illuminate\Filesystem\Filesystem();
        $controllerPath = app_path('Http/Controllers');
        if (! is_dir($controllerPath)) {
            return $this->pass('No controllers directory.');
        }

        $broken = [];
        $patterns = ['/public function store/', '/public function update/', '/public function destroy/'];

        foreach ($fs->allFiles($controllerPath) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            $mutating = false;
            foreach ($patterns as $p) {
                if (preg_match($p, $contents)) $mutating = true;
            }

            if (! $mutating) continue;

            // Look for validation methods.
            $hasValidate = preg_match('/(->validate\s*\(|FormRequest|::make\s*\([^)]*\)\s*->validate|validateRequest\()/i', $contents);
            if (! $hasValidate) {
                $broken[] = $file->getRelativePathname();
            }
        }

        if ($broken) {
            return $this->warning(
                'Controllers with store/update/destroy but no validation: ' . implode(', ', $broken),
                ['files' => $broken]
            );
        }

        return $this->pass('All mutating controllers apply validation.');
    }
}
