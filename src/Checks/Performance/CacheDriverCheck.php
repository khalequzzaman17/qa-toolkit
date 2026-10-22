<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class CacheDriverCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'CACHE_DRIVER is not array/file in production'; }

    public function run()
    {
        $env   = (string) config('app.env');
        $driver = (string) config('cache.default');

        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        if (in_array($driver, ['array', 'file', 'database'], true)) {
            return $this->warning(
                "CACHE_DRIVER = '{$driver}' in production; use redis/memcached for distributed workloads.",
                ['driver' => $driver]
            );
        }

        return $this->pass("CACHE_DRIVER = '{$driver}'.");
    }
}
