<?php

namespace QaToolkit\Console\Commands;

class QaInfraCommand extends BaseQaCommand
{
    protected $signature = 'qa:infra {--report}';
    protected $description = 'Run the infra/deploy suite only (env, migrations, cron, backups, ...)';

    protected function filter(string $class): bool
    {
        return str_starts_with($class, 'QaToolkit\\Checks\\Infra\\');
    }

    public function handle(): int
    {
        $this->info('QA Toolkit · Infra / Deploy suite');
        $report = $this->runChecks();
        return $this->summarize($report);
    }
}
