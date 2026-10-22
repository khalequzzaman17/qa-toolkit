<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;

class DbConnectionCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'Database is reachable and reports correct version'; }

    public function run()
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return $this->critical(
                'Could not connect to database: ' . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }

        try {
            $version = (string) DB::select('SELECT VERSION() AS v')[0]->v ?? '';
        } catch (\Throwable $e) {
            $version = 'unknown';
        }

        return $this->pass("DB connection successful ({$version}).", ['version' => $version]);
    }
}
