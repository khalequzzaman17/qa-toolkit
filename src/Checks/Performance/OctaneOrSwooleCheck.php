<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class OctaneOrSwooleCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'High-throughput app uses Octane/Swoole (optional)'; }

    public function run()
    {
        $composer = base_path('composer.json');
        if (! file_exists($composer)) {
            return $this->pass('No composer.json — skipping.');
        }

        $contents = json_decode((string) file_get_contents($composer), true) ?: [];
        $required = array_keys(array_merge($contents['require'] ?? [], $contents['require-dev'] ?? []));

        $hasOctane = false;
        foreach ($required as $pkg) {
            if ($pkg === 'laravel/octane') {
                $hasOctane = true; break;
            }
        }

        if ($hasOctane) {
            return $this->pass('Laravel Octane is installed.', ['package' => 'laravel/octane']);
        }

        return $this->warning(
            'Laravel Octane not detected — consider it for high-throughput apps (>200 rps).'
        );
    }
}
