<?php

namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;

class SqlInjectionSinkCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'No DB::raw($input) or where($col, $input-as-sql) sinks'; }

    public function run()
    {
        $fs = new \Illuminate\Filesystem\Filesystem();
        $app = app_path();

        if (! is_dir($app)) {
            return $this->pass('No app/ directory.');
        }

        $patterns = [
            '/DB::raw\(\s*\$[a-zA-Z_]/',
            '/DB::select\(\s*\$[a-zA-Z_]/',
            '/DB::statement\(\s*\$[a-zA-Z_]/',
            '/whereRaw\(\s*\$[a-zA-Z_]/',
            '/selectRaw\(\s*\$[a-zA-Z_]/',
            '/orderByRaw\(\s*\$[a-zA-Z_]/',
            '/groupByRaw\(\s*\$[a-zA-Z_]/',
        ];

        $offenders = [];
        foreach ($fs->allFiles($app) as $file) {
            if (! in_array($file->getExtension(), ['php'], true)) continue;
            $contents = file_get_contents($file->getRealPath()) ?: '';
            foreach ($patterns as $p) {
                if (preg_match($p, $contents)) {
                    $line = $this->lineOf($contents, $p);
                    $offenders[] = $file->getRelativePathname() . ':' . $line;
                }
            }
        }

        if ($offenders) {
            return $this->error(
                'Possible SQL injection sinks: ' . implode(', ', array_slice($offenders, 0, 8)),
                ['offenders' => $offenders]
            );
        }

        return $this->pass('No raw SQL with user input detected.');
    }

    private function lineOf(string $contents, string $pattern): int
    {
        if (preg_match($pattern, $contents, $m, PREG_OFFSET_CAPTURE)) {
            return substr_count(substr($contents, 0, $m[0][1]), "\n") + 1;
        }
        return 0;
    }
}
