<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class SessionDriverCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'SESSION_DRIVER is not file/cookie in production multi-server'; }

    public function run()
    {
        $env = (string) config('app.env');
        $drv = (string) config('session.driver');

        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        if (in_array($drv, ['file', 'cookie'], true)) {
            return $this->warning(
                "SESSION_DRIVER = '{$drv}' in production; use database/redis for multi-instance.",
                ['driver' => $drv]
            );
        }

        return $this->pass("SESSION_DRIVER = '{$drv}'.");
    }
}
