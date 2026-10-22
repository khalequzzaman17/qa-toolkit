<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Route;

class OpenRedirectCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No redirect()->to($input) without a host allow-list'; }

    public function run()
    {
        $fs = new \Illuminate\Filesystem\Filesystem();
        $controllers = app_path('Http/Controllers');
        if (! is_dir($controllers)) {
            return $this->pass('No controllers directory.');
        }

        $offenders = [];
        $patterns = [
            '/->redirect\(\s*request\(\s*[^)]+\s*\)/',
            '/->redirect\(\s*\$request\s*->\s*(input|get|query|post)\s*\(/',
            '/->away\(\s*\$request/',
            '/Redirect::to\(\s*\$request/',
        ];

        foreach ($fs->allFiles($controllers) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            foreach ($patterns as $p) {
                if (preg_match($p, $contents)) {
                    $offenders[] = $file->getRelativePathname();
                }
            }
        }

        if ($offenders) {
            return $this->warning(
                'Possible open-redirect sinks in controllers: ' . implode(', ', array_unique($offenders)),
                ['files' => array_values(array_unique($offenders))]
            );
        }

        return $this->pass('No open-redirect sinks detected.');
    }
}
