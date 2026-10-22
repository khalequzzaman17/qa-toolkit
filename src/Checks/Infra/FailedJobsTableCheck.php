<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;

class FailedJobsTableCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'failed_jobs table exists and is queryable'; }

    public function run()
    {
        $conn = config('database.default');
        $prefix = config("database.connections.{$conn}.prefix", '');

        try {
            $exists = \Illuminate\Support\Facades\Schema::hasTable($prefix . 'failed_jobs');
        } catch (\Throwable $e) {
            return $this->warning('Could not inspect schema for failed_jobs table: ' . $e->getMessage());
        }

        if (! $exists) {
            return $this->critical(
                'failed_jobs table missing — run `php artisan queue:failed-table && php artisan migrate`.'
            );
        }

        return $this->pass('failed_jobs table exists.');
    }
}
