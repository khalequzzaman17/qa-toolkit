<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class OpcacheStatusCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'OPcache enabled in production (fpm)'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        if (! function_exists('opcache_get_status')) {
            return $this->warning('OPcache extension not installed.');
        }

        $status = opcache_get_status(false);

        if (! is_array($status) || ! ($status['opcache_enabled'] ?? false)) {
            return $this->warning('OPcache is loaded but not enabled.');
        }

        return $this->pass('OPcache enabled.', ['memory_used' => $status['memory_usage']['used_memory'] ?? null]);
    }
}
