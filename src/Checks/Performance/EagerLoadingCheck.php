<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class EagerLoadingCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'with() or load() present where ->load() is missing'; }

    public function run()
    {
        $fs = new Filesystem();
        $paths = [app_path('Http/Controllers'), app_path('Livewire'), app_path('Actions')];

        $heavy = [];
        foreach ($paths as $p) {
            if (! is_dir($p)) continue;
            foreach ($fs->allFiles($p) as $file) {
                $contents = file_get_contents($file->getRealPath()) ?: '';
                if (preg_match('/->paginate\(|->get\(\s*\)|->all\(\s*\)|->cursor\(\s*\)/', $contents)
                    && ! preg_match('/\bwith\b\s*\(|\bload\b\s*\(|\bloadCount\b\s*\(/', $contents)
                ) {
                    $heavy[] = $file->getRelativePathname();
                }
            }
        }

        if ($heavy) {
            return $this->warning(
                'List queries without eager loading: ' . implode(', ', array_slice($heavy, 0, 5)),
                ['files' => $heavy]
            );
        }

        return $this->pass('All list queries eagerly load relations.');
    }
}
