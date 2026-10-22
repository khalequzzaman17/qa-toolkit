<?php

namespace QaToolkit\Checks\Functional;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class JobsHaveTriesCheck extends Check
{
    public function suite(): string { return 'functional'; }
    public function label(): string { return 'Job classes define $tries / $backoff for retries'; }

    public function run()
    {
        $path = app_path('Jobs');
        if (! is_dir($path)) {
            return $this->pass('No app/Jobs directory.');
        }

        $fs = new Filesystem();
        $missing = [];

        foreach ($fs->allFiles($path) as $file) {
            $contents = file_get_contents($file->getRealPath()) ?: '';

            // Look for class declaration.
            if (! preg_match('/class\s+(\w+)/', $contents, $m)) continue;
            $class = 'App\\Jobs\\' . $m[1];

            if (! class_exists($class)) continue;
            if (! is_subclass_of($class, \Illuminate\Contracts\Queue\ShouldQueue::class)) continue;

            // Skip jobs that explicitly opt out via should not retry.
            if (preg_match('/public\s+\$tries\s*=/', $contents)) continue;
            if (preg_match('/public\s+\$backoff\s*=/', $contents)) continue;
            if (preg_match('/public\s+function\s+(retryUntil|backoff)\s*\(/', $contents)) continue;

            $missing[] = $class;
        }

        if ($missing) {
            return $this->warning(
                'Queueable jobs without $tries/$backoff: ' . implode(', ', array_slice($missing, 0, 5)),
                ['missing' => $missing]
            );
        }

        return $this->pass('All queueable jobs define retry policy.');
    }
}
