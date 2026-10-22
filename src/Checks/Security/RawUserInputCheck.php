<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class RawUserInputCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No raw user input echoed in Blade (XSS-prone)'; }

    public function run()
    {
        $fs     = new Filesystem();
        $views  = resource_path('views');

        if (! is_dir($views)) {
            return $this->pass('No resources/views directory.');
        }

        $offenders = [];
        $patterns  = [
            '/\{!!\s*request\(/',       // {!! request('x') !!}
            '/\{!!\s*\$request/',
            '/\{!!\s*old\(/',
            '/\{!!\s*\$_GET/',
            '/\{!!\s*\$_POST/',
            '/\{!!\s*\$_REQUEST/',
            '/<\?php\s+echo\s+\$_/',
            '/echo\s+\$_GET/',
        ];

        foreach ($fs->allFiles($views) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $contents)) {
                    $offenders[] = $file->getRelativePathname() . ' (' . $pattern . ')';
                }
            }
        }

        if ($offenders) {
            return $this->error(
                'Raw user input detected in Blade templates: ' . implode(', ', $offenders),
                ['offenders' => $offenders]
            );
        }

        return $this->pass('No raw user input echoed via {!! !!} in Blade.');
    }
}
