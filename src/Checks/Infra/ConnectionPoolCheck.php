<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;

class ConnectionPoolCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Persistent DB connections are below the configured pool size'; }

    public function run()
    {
        $driver = config('database.connections.' . config('database.default') . '.driver');
        if ($driver !== 'mysql') {
            return $this->pass("Skipping — DB driver is {$driver}.");
        }

        try {
            $count = DB::select("SHOW STATUS WHERE Variable_name = 'Threads_connected'")[0]->Value ?? 0;
            $max   = DB::select("SHOW VARIABLES WHERE Variable_name = 'max_connections'")[0]->Value ?? 0;
        } catch (\Throwable $e) {
            return $this->warning('Could not query MySQL connection state.');
        }

        $count = (int) $count;
        $max   = (int) $max;

        if ($max > 0 && $count > $max * 0.8) {
            return $this->warning(
                "MySQL active connections = {$count}/{$max} (over 80% saturation).",
                ['active' => $count, 'max' => $max]
            );
        }

        return $this->pass("MySQL active connections = {$count}/{$max}.", ['active' => $count, 'max' => $max]);
    }
}
