<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use QaToolkit\Support\Cfg;
use Illuminate\Filesystem\Filesystem;

class MigrationSizeCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'No migration file exceeds threshold (refactor big)'; }

    public function run()
    {
        $threshold = (int) Cfg::threshold('migration_size_kb');
        if ($threshold === 0) $threshold = 50;

        $dir = base_path('database/migrations');
        if (! is_dir($dir)) {
            return $this->pass('No migrations directory.');
        }

        $fs = new Filesystem();
        $offenders = [];

        foreach ($fs->allFiles($dir) as $file) {
            $sizeKB = (int) round(filesize($file->getRealPath()) / 1024);
            if ($sizeKB > $threshold) {
                $offenders[] = $file->getFilename() . " ({$sizeKB} KB)";
            }
        }

        if ($offenders) {
            return $this->warning(
                'Migration files exceed ' . $threshold . 'KB: ' . implode(', ', $offenders),
                ['offenders' => $offenders, 'threshold_kb' => $threshold]
            );
        }

        return $this->pass("All migration files ≤ {$threshold}KB.");
    }
}
