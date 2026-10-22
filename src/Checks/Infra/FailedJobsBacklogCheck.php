<?php

namespace QaToolkit\Checks\Infra;

use QaToolkit\Checks\Check;
use Illuminate\Support\Facades\DB;

class FailedJobsBacklogCheck extends Check
{
    public function suite(): string { return 'infra'; }
    public function label(): string { return 'No backlog of failed jobs (> 50 in last 7 days)'; }

    public function run()
    {
        try {
            $count = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDays(7)->toDateTimeString())
                ->count();
        } catch (\Throwable $e) {
            return $this->warning('Could not query failed_jobs table.');
        }

        if ($count > 50) {
            return $this->error(
                "{$count} failed jobs in the last 7 days — investigate and retry them.",
                ['count' => $count]
            );
        }

        if ($count > 0) {
            return $this->warning("{$count} failed jobs in the last 7 days.", ['count' => $count]);
        }

        return $this->pass('No failed jobs in the last 7 days.');
    }
}
