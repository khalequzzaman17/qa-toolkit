<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class LazyLoadingInRoutesCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'No "->\$model->relation" calls likely to N+1 in controllers'; }

    public function run()
    {
        $fs = new Filesystem();
        $paths = [app_path('Http/Controllers'), app_path('Livewire')];

        $offenders = [];
        $patterns = [
            '/->\w+\s*->\s*forEach/',
            '/->\w+\s*->\s*map\s*\(/',
            '/->\w+\s*->\s*filter\s*\(/',
            '/->\w+\s*->\s*each\s*\(/',
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) continue;
            foreach ($fs->allFiles($path) as $file) {
                $contents = file_get_contents($file->getRealPath()) ?: '';
                foreach ($patterns as $p) {
                    if (preg_match($p, $contents, $m, PREG_OFFSET_CAPTURE)) {
                        $line = substr_count(substr($contents, 0, $m[0][1]), "\n") + 1;
                        $offenders[] = $file->getRelativePathname() . ':' . $line;
                    }
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Possible N+1 patterns in controllers/Livewire: ' . implode(', ', array_slice($offenders, 0, 8)),
                ['offenders' => $offenders]
            );
        }

        return $this->pass('No obvious N+1 patterns in controllers.');
    }
}
