<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class UploadsValidatedCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'File upload routes validate mimes and size'; }

    public function run()
    {
        $fs = new Filesystem();
        $controllers = app_path('Http/Controllers');
        if (! is_dir($controllers)) {
            return $this->pass('No controllers directory.');
        }

        $offenders = [];
        foreach ($fs->allFiles($controllers) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';

            if (str_contains($contents, '->file(') || str_contains($contents, '->hasFile(')) {
                if (! str_contains($contents, "'mimes'") && ! str_contains($contents, '"mimes"') && ! str_contains($contents, "'max'") && ! str_contains($contents, '"max"')) {
                    $offenders[] = $file->getRelativePathname();
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Upload-handling controllers without mimes/max validation: ' . implode(', ', array_unique($offenders)),
                ['files' => array_values(array_unique($offenders))]
            );
        }

        return $this->pass('All upload routes validate mimes and max size.');
    }
}
