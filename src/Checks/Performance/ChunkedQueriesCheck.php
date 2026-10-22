<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ChunkedQueriesCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Large bulk queries use chunk()/cursor() not ->get()'; }

    public function run()
    {
        $fs = new Filesystem();
        $paths = [app_path(), database_path('seeders')];
        $offenders = [];

        foreach ($paths as $p) {
            if (! is_dir($p)) continue;
            foreach ($fs->allFiles($p) as $file) {
                if ($file->getExtension() !== 'php') continue;
                $contents = file_get_contents($file->getRealPath()) ?: '';

                // Look for "Model::all()" or "::get()" calls without chunk/cursor nearby.
                if (preg_match('/::(all|get)\s*\(\s*\)/', $contents, $m)
                    && ! preg_match('/(chunk|cursor|lazy)\s*\(/', $contents)) {
                    $offenders[] = $file->getRelativePathname() . ' (uses ' . $m[0] . ')';
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Bulk fetches without chunk/cursor: ' . implode(', ', array_slice($offenders, 0, 5)),
                ['offenders' => $offenders]
            );
        }

        return $this->pass('No large bulk fetches detected.');
    }
}
