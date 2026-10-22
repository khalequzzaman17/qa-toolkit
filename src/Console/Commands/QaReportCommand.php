<?php

namespace QaToolkit\Console\Commands;

use QaToolkit\Reports\Report;
use QaToolkit\Support\Cfg;

class QaReportCommand extends BaseQaCommand
{
    protected $signature = 'qa:report
                            {--suite= : limit to a specific suite}
                            {--format=json : json|markdown|plain}
                            {--path= : file to write (defaults to storage/app/qa-report.{ext})}';

    protected $description = 'Run all checks and emit a machine-readable report (CI-friendly).';

    public function handle(): int
    {
        $this->info('QA Toolkit · generating report');
        $report = $this->runChecks();

        $format = (string) $this->option('format');
        $path   = (string) $this->option('path') ?: Cfg::outputPath() ?: storage_path("app/qa-report.{$format}");

        if (! is_dir(dirname($path))) {
            @mkdir(dirname($path), 0775, true);
        }

        $contents = match ($format) {
            'json'     => $report->toJson(),
            'markdown' => $report->toMarkdown(),
            'plain'    => $report->toMarkdown(),
            default    => $report->toJson(),
        };

        file_put_contents($path, $contents);

        $this->info('Report written to ' . $path);
        $this->line($report->summaryLine());

        return $report->failsOn(Cfg::failOn()) ? 1 : 0;
    }

    protected function filter(string $class): bool
    {
        if ($suite = (string) $this->option('suite')) {
            return stripos($class, '\\' . ucfirst($suite) . '\\') !== false;
        }
        return true;
    }
}
