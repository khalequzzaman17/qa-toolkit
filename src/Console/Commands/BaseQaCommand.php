<?php

namespace QaToolkit\Console\Commands;

use Illuminate\Console\Command;
use QaToolkit\Checks\Check;
use QaToolkit\Reports\Report;
use QaToolkit\Support\CheckResult;
use QaToolkit\Support\Cfg;

abstract class BaseQaCommand extends Command
{
    /**
     * Filter to apply to checks when running this command.
     * Override in subclasses — e.g. return only security checks.
     *
     * @param class-string<Check> $class
     */
    abstract protected function filter(string $class): bool;

    protected function runChecks(): Report
    {
        /** @var array<class-string, Check> $checks */
        $all = app('qa-toolkit.checks');
        $report = app(Report::class);

        foreach ($all as $class => $check) {
            if (! $this->filter($class)) {
                continue;
            }

            $this->line("  <fg=gray>→</> " . $check->label());

            try {
                $result = $check->run();
            } catch (\Throwable $e) {
                $result = CheckResult::error($check->suite(), $check->id(), 'Check threw: ' . $e->getMessage(), [
                    'exception' => $e::class,
                    'file'      => $e->getFile() . ':' . $e->getLine(),
                ]);
            }

            $this->renderResult($result);
            $report->add($result);
        }

        return $report;
    }

    protected function renderResult(CheckResult $r): void
    {
        $color = match ($r->severity) {
            CheckResult::PASS     => 'green',
            CheckResult::WARNING  => 'yellow',
            CheckResult::ERROR    => 'red',
            CheckResult::CRITICAL => 'red;options=bold',
            default               => 'gray',
        };

        $prefix = match ($r->severity) {
            CheckResult::PASS     => '✓',
            CheckResult::WARNING  => '!',
            CheckResult::ERROR    => '✗',
            CheckResult::CRITICAL => '✗',
            default               => '?',
        };

        $this->line("    <fg={$color}>{$prefix} [{$r->severity}]</> {$r->message}");
    }

    protected function summarize(Report $report): int
    {
        $this->newLine();
        $this->line('  <fg=cyan>───</>');
        $this->info($report->summaryLine());

        $fails = Cfg::failOn();

        if ($report->failsOn($fails)) {
            $this->error("QA failed — worst severity is {$report->worstSeverity()} (fails on {$fails}).");
            return 1;
        }

        $this->info('QA passed.');
        return 0;
    }
}
