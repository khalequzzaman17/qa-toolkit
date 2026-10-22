<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;

class ChattyLogCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'LOG_LEVEL is not debug/trace in production'; }

    public function run()
    {
        $env = (string) config('app.env');
        if ($env !== 'production') {
            return $this->pass("Skipping — env is {$env}.");
        }

        $level = (string) config('logging.channels.stack.level', config('app.log_level', 'info'));
        $level = strtolower($level);

        if (in_array($level, ['debug', 'trace', 'all'], true)) {
            return $this->warning(
                "LOG_LEVEL = '{$level}' in production — disk I/O will be heavy.",
                ['level' => $level]
            );
        }

        return $this->pass("LOG_LEVEL = '{$level}'.");
    }
}
