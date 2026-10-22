<?php

namespace QaToolkit\Console\Commands;

class QaPerformanceCommand extends BaseQaCommand
{
    protected $signature = 'qa:performance {--report}';
    protected $description = 'Run the performance suite only (N+1, cache, queue, indexes, ...)';

    protected function filter(string $class): bool
    {
        return str_starts_with($class, 'QaToolkit\\Checks\\Performance\\');
    }

    public function handle(): int
    {
        $this->info('QA Toolkit · Performance suite');
        $report = $this->runChecks();
        return $this->summarize($report);
    }
}
