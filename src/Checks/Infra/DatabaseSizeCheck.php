<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSizeCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Largest table is < 50 GB (capacity signal)'; }

    public function run()
    {
        $conn = config('database.default');
        $driver = config("database.connections.{$conn}.driver");

        if ($driver !== 'mysql') {
            return $this->pass("Skipping — DB driver is {$driver}.");
        }

        try {
            $rows = DB::select("
                SELECT table_name AS t, ROUND((data_length + index_length) / 1024 / 1024, 1) AS mb
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                ORDER BY mb DESC LIMIT 5
            ");
        } catch (\Throwable $e) {
            return $this->warning('Could not query information_schema for table sizes.');
        }

        if (empty($rows)) {
            return $this->pass('No tables found.');
        }

        $largest = $rows[0];
        $mb = (float) $largest->mb;
        if ($mb > 50 * 1024) {
            return $this->warning(
                "Largest table '{$largest->t}' is {$mb} MB — consider partitioning or archiving.",
                ['largest_table' => $largest->t, 'mb' => $mb]
            );
        }

        return $this->pass("Largest table '{$largest->t}' is {$mb} MB.", ['largest_table' => $largest->t, 'mb' => $mb]);
    }
}
