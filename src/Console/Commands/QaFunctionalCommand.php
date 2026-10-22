<?php

namespace QaToolkit\Console\Commands;

class QaFunctionalCommand extends BaseQaCommand
{
    protected $signature = 'qa:functional {--report}';
    protected $description = 'Run the functional suite only (routes, controllers, validation, ...)';

    protected function filter(string $class): bool
    {
        return str_starts_with($class, 'QaToolkit\\Checks\\Functional\\');
    }

    public function handle(): int
    {
        $this->info('QA Toolkit · Functional suite');
        $report = $this->runChecks();
        return $this->summarize($report);
    }
}
