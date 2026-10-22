<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class AssetCountCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Public assets total < 1000 files (housekeeping)'; }

    public function run()
    {
        $public = public_path();
        if (! is_dir($public)) {
            return $this->pass('No public directory.');
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($public, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            $count = iterator_count($iterator);
        } catch (\Throwable $e) {
            return $this->warning('Could not iterate public/: ' . $e->getMessage());
        }

        if ($count > 5000) {
            return $this->warning(
                "{$count} files in public/ — review for stale build artifacts.",
                ['count' => $count]
            );
        }

        return $this->pass("{$count} files in public/.", ['count' => $count]);
    }
}
