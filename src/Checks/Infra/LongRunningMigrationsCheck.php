<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Schema;

class LongRunningMigrationsCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'No migrations modify large tables without batching (heuristic)'; }

    public function run()
    {
        $dir = base_path('database/migrations');
        if (! is_dir($dir)) {
            return $this->pass('No migrations directory.');
        }

        $fs = new \Illuminate\Filesystem\Filesystem();
        $offenders = [];

        foreach ($fs->allFiles($dir) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';

            // Heuristic: ALTER TABLE on a string column without ->change()->default(...).
            $hasAlter = preg_match('/->table\s*\(|Schema::table\s*\(/', $contents);
            $hasBigBatch = preg_match('/->integer\(|->string\(|->boolean\(|->boolean/', $contents)
                && ! preg_match('/->default\(/', $contents)
                && ! preg_match('/->nullable\(/', $contents);

            if ($hasAlter && $hasBigBatch) {
                $offenders[] = $file->getFilename();
            }
        }

        if ($offenders) {
            return $this->warning(
                'Migrations that may lock the table for long on large data: ' . implode(', ', array_slice($offenders, 0, 5)),
                ['offenders' => $offenders]
            );
        }

        return $this->pass('No risky ALTER migrations detected.');
    }
}
