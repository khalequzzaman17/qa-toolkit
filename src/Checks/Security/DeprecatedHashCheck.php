<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class DeprecatedHashCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No md5/sha1 password hashing or token signing in app/'; }

    public function run()
    {
        $fs = new \Illuminate\Filesystem\Filesystem();
        $app = app_path();

        if (! is_dir($app)) {
            return $this->pass('No app/ directory.');
        }

        $offenders = [];
        $patterns = [
            '/md5\(\s*\$[a-zA-Z_].*password/i',
            '/sha1\(\s*\$[a-zA-Z_].*password/i',
            '/md5\(\s*request\(/i',
            '/sha1\(\s*request\(/i',
        ];

        foreach ($fs->allFiles($app) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            foreach ($patterns as $p) {
                if (preg_match($p, $contents)) {
                    $offenders[] = $file->getRelativePathname();
                }
            }
        }

        if ($offenders) {
            return $this->error(
                'Deprecated md5/sha1 hashing detected in app/: ' . implode(', ', array_values(array_unique($offenders))),
                ['files' => array_values(array_unique($offenders))]
            );
        }

        return $this->pass('No deprecated md5/sha1 hashing in app/.');
    }
}
