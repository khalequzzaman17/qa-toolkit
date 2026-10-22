<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class EvalCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No eval() / assert() / create_function in app/'; }

    public function run()
    {
        $fs = new Filesystem();
        $app = app_path();

        if (! is_dir($app)) {
            return $this->pass('No app/ directory.');
        }

        $offenders = [];
        $patterns = [
            '/\beval\s*\(/',
            '/\bassert\s*\(/',
            '/\bcreate_function\s*\(/',
            '/preg_replace\s*\(\s*[\'"][^\'"]*[\/#][a-z]*e[a-z]*[\/#]/i', // /e modifier
        ];

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
            return $this->critical(
                'eval/assert/create_function sinks detected (RCE-prone): ' . implode(', ', array_unique($offenders)),
                ['offenders' => array_values(array_unique($offenders))]
            );
        }

        return $this->pass('No eval/assert/create_function in app/.');
    }
}
