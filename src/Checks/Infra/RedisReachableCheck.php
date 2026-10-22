<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\Redis;

class RedisReachableCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Redis (cache/queue) is reachable when configured'; }

    public function run()
    {
        $cache = (string) config('cache.default');
        $queue = (string) config('queue.default');

        $usesRedis = ($cache === 'redis') || ($queue === 'redis');

        if (! $usesRedis) {
            return $this->pass('Redis not used by cache/queue; skipping.');
        }

        try {
            $pong = Redis::connection()->ping();
            if (is_object($pong) && method_exists($pong, 'getPayload')) {
                $pong = $pong->getPayload();
            }
            return $this->pass('Redis responded to PING.', ['response' => (string) $pong]);
        } catch (\Throwable $e) {
            return $this->critical('Redis connection failed: ' . $e->getMessage(), ['error' => $e->getMessage()]);
        }
    }
}
