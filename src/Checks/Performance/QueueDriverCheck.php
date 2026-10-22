<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;

class QueueDriverCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'QUEUE_CONNECTION is sync only in local/test'; }

    public function run()
    {
        $env = (string) config('app.env');
        $drv = (string) config('queue.default');

        if ($env === 'production' && $drv === 'sync') {
            return $this->error(
                'QUEUE_CONNECTION = sync in production — every job blocks the HTTP request.',
                ['driver' => $drv]
            );
        }

        if ($env !== 'production' && $drv === 'sync') {
            return $this->pass("QUEUE_CONNECTION = sync (allowed in {$env}).");
        }

        return $this->pass("QUEUE_CONNECTION = '{$drv}'.");
    }
}
