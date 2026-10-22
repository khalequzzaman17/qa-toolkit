<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class LogLevelConfiguredCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'LOG_CHANNEL is daily (not single) in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $channel = (string) config('logging.default', config('app.log_channel'));

        if (in_array($channel, ['single', 'stderr', 'emergency'], true)) {
            return $this->warning(
                "LOG_CHANNEL = '{$channel}' — use 'daily' for log rotation.",
                ['channel' => $channel]
            );
        }

        return $this->pass("LOG_CHANNEL = '{$channel}'.");
    }
}
