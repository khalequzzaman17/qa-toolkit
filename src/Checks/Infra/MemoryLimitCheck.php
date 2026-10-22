<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class MemoryLimitCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'php.ini memory_limit is reasonable (>= 128M)'; }

    public function run()
    {
        $limit = ini_get('memory_limit');

        if ($limit === '-1') {
            return $this->warning('memory_limit = -1 (unlimited) — set an explicit limit to avoid OOM taking down the host.');
        }

        if (preg_match('/^(\d+)([KMG]?)/i', (string) $limit, $m)) {
            $value = (int) $m[1];
            $unit  = strtoupper($m[2] ?? '');
            $bytes = match ($unit) {
                'G' => $value * 1024 * 1024 * 1024,
                'M' => $value * 1024 * 1024,
                'K' => $value * 1024,
                default => $value,
            };

            if ($bytes < 128 * 1024 * 1024) {
                return $this->warning("memory_limit = {$limit}; consider 128M or more for Laravel.");
            }
        }

        return $this->pass("memory_limit = {$limit}.");
    }
}
