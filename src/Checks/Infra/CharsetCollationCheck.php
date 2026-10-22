<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;

class CharsetCollationCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'MySQL charset is utf8mb4 with utf8mb4_unicode_ci collation'; }

    public function run()
    {
        $conn = config('database.default');
        $driver = config("database.connections.{$conn}.driver");

        if ($driver !== 'mysql') {
            return $this->pass("Skipping — DB driver is {$driver}.");
        }

        try {
            $row = DB::select("SELECT @@character_set_database AS charset, @@collation_database AS collation")[0] ?? null;
        } catch (\Throwable $e) {
            return $this->warning('Could not query DB charset: ' . $e->getMessage());
        }

        if (! $row) {
            return $this->warning('Could not determine DB charset.');
        }

        $charset = (string) $row->charset;
        $collation = (string) $row->collation;

        if (! str_starts_with($charset, 'utf8mb4')) {
            return $this->error(
                "DB charset = '{$charset}'; switch to 'utf8mb4' for emoji/CJK support.",
                ['charset' => $charset, 'collation' => $collation]
            );
        }

        if (! str_starts_with($collation, 'utf8mb4_unicode_ci') && ! str_starts_with($collation, 'utf8mb4_0900_ai_ci')) {
            return $this->warning(
                "DB collation = '{$collation}'; consider utf8mb4_unicode_ci or utf8mb4_0900_ai_ci.",
                ['charset' => $charset, 'collation' => $collation]
            );
        }

        return $this->pass("DB charset={$charset}, collation={$collation}.");
    }
}
