<?php

namespace QaToolkit\Checks\Performance;

use QaToolkit\Checks\Check;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

class MissingIndexCheck extends Check
{
    public function suite(): string { return 'performance'; }
    public function label(): string { return 'Foreign-key columns have indexes (avoid table scans)'; }

    public function run()
    {
        $conn = config('database.default');
        $driver = config("database.connections.{$conn}.driver");

        if ($driver !== 'mysql') {
            return $this->pass("Skipping — DB driver is {$driver}, only MySQL is supported here.");
        }

        try {
            $tables = DB::select("SHOW TABLES");
        } catch (\Throwable $e) {
            return $this->warning('Could not query information schema: ' . $e->getMessage());
        }

        $keyColumn = sprintf('Tables_in_%s', config("database.connections.{$conn}.database"));
        $missing = [];

        foreach ($tables as $table) {
            $name = (string) $table->$keyColumn;

            // List FK columns.
            $fks = DB::select("
                SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME <> 'PRIMARY'
            ", [$name]);

            $fkCols = array_map(fn ($r) => $r->COLUMN_NAME, $fks);

            // For each FK column, verify there's an index covering it.
            foreach ($fkCols as $col) {
                $idx = DB::select("
                    SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
                    LIMIT 1
                ", [$name, $col]);

                if (empty($idx)) {
                    $missing[] = "{$name}.{$col}";
                }
            }
        }

        $missing = array_slice($missing, 0, 25);

        if ($missing) {
            return $this->warning(
                'Foreign-key columns without an index (slow joins): ' . implode(', ', $missing),
                ['missing' => $missing]
            );
        }

        return $this->pass('All foreign-key columns have an index.');
    }
}
