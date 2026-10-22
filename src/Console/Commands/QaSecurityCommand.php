<?php

namespace QaToolkit\Console\Commands;

class QaSecurityCommand extends BaseQaCommand
{
    protected $signature = 'qa:security {--report}';
    protected $description = 'Run the security suite only (auth, CSRF, headers, secrets, mass-assignment, ...)';

    protected function filter(string $class): bool
    {
        return str_starts_with($class, 'QaToolkit\\Checks\\Security\\');
    }

    public function handle(): int
    {
        $this->info('QA Toolkit · Security suite');
        $report = $this->runChecks();
        return $this->summarize($report);
    }
}
