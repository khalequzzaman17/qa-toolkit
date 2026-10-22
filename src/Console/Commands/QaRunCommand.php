<?php

namespace QaToolkit\Console\Commands;

use QaToolkit\Support\Cfg;
use QaToolkit\Support\CheckResult;
use QaToolkit\Reports\Report;

class QaRunCommand extends BaseQaCommand
{
    protected $signature = 'qa:run
                            {--suite= : only run a specific suite (security|performance|functional|infra)}
                            {--format= : output format: table, json, markdown, plain}
                            {--report= : file path to write the full report}
                            {--stop-on-failure : stop at the first failing check}';

    protected $description = 'Run all enabled QA suites — your one-command pre-launch gate.';

    public function handle(): int
    {
        $this->info('QA Toolkit · pre-launch sweep');
        $this->line('');

        $report = $this->runChecks();

        // Optional: write a sidecar report.
        if ($path = $this->option('report')) {
            $this->writeReportFile($report, $path);
        }

        return $this->summarize($report);
    }

    protected function filter(string $class): bool
    {
        if ($suite = (string) $this->option('suite')) {
            return stripos($class, '\\' . ucfirst($suite) . '\\') !== false;
        }

        // Run only suites enabled in config.
        foreach (['security', 'performance', 'functional', 'infra'] as $suite) {
            if (stripos($class, '\\' . ucfirst($suite) . '\\') !== false) {
                return Cfg::enabled($suite);
            }
        }
        return false;
    }

    protected function writeReportFile(Report $report, string $path): void
    {
        $format = (string) $this->option('format') ?: Cfg::format();
        $ext = match ($format) {
            'json'     => 'json',
            'markdown' => 'md',
            'plain'    => 'txt',
            default    => 'txt',
        };

        if (! str_ends_with($path, ".{$ext}")) {
            $path = rtrim($path, '/') . '/qa-report.' . $ext;
        }

        $contents = match ($format) {
            'json'     => $report->toJson(),
            'markdown' => $report->toMarkdown(),
            default    => $report->toJson() . "\n\n" . $report->toMarkdown(),
        };

        if (! is_dir(dirname($path))) {
            @mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, $contents);
        $this->line('');
        $this->info("Wrote report → {$path}");
    }
}
