<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class DdLeftoverCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No dd()/dump()/var_dump() left in app/ source'; }

    public function run()
    {
        $fs = new \Illuminate\Filesystem\Filesystem();
        $app = app_path();

        if (! is_dir($app)) {
            return $this->pass('No app/ directory.');
        }

        $offenders = [];
        $patterns = ['/[^a-z_]dd\s*\(/', '/\bdump\s*\(/', '/\bvar_dump\s*\(/', '/\bprint_r\s*\(/', '/exit\s*\(/', '/\bdie\s*\(/'];

        foreach ($fs->allFiles($app) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            foreach ($patterns as $p) {
                if (preg_match($p, $contents, $m, PREG_OFFSET_CAPTURE)) {
                    $line = substr_count(substr($contents, 0, $m[0][1]), "\n") + 1;
                    $offenders[] = $file->getRelativePathname() . ':' . $line;
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Debug statements left in app/: ' . implode(', ', array_slice(array_unique($offenders), 0, 8)),
                ['offenders' => array_values(array_unique($offenders))]
            );
        }

        return $this->pass('No debug statements in app/.');
    }
}
