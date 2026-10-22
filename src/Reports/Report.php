<?php

namespace QaToolkit\Reports;

use QaToolkit\Support\CheckResult;

/**
 * Aggregates CheckResult objects into a structured report that Artisan
 * commands can render in multiple formats (table, JSON, markdown).
 */
final class Report
{
    /** @var CheckResult[] */
    private array $results = [];

    public function add(CheckResult $result): void
    {
        $this->results[] = $result;
    }

    /** @return CheckResult[] */
    public function all(): array
    {
        return $this->results;
    }

    /**
     * Results grouped by suite, preserving insertion order.
     *
     * @return array<string, CheckResult[]>
     */
    public function bySuite(): array
    {
        $grouped = [];

        foreach ($this->results as $result) {
            $grouped[$result->suite][] = $result;
        }

        return $grouped;
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function countBySeverity(string $severity): int
    {
        return count(array_filter(
            $this->results,
            fn (CheckResult $r) => $r->severity === $severity
        ));
    }

    /**
     * The worst-severity encountered across all results.
     */
    public function worstSeverity(): string
    {
        $worst = CheckResult::PASS;
        $weight = 0;

        foreach ($this->results as $result) {
            if ($result->weight() > $weight) {
                $weight = $result->weight();
                $worst  = $result->severity;
            }
        }

        return $worst;
    }

    /**
     * True if the report contains any result whose severity matches or
     * exceeds the configured "fail_on" threshold.
     */
    public function failsOn(string $threshold): bool
    {
        $weight = match ($threshold) {
            'critical' => 3,
            'error'    => 2,
            'warning'  => 1,
            'none'     => 999, // never fail
            default    => 1,
        };

        foreach ($this->results as $result) {
            if ($result->weight() >= $weight) {
                return true;
            }
        }

        return false;
    }

    public function summaryLine(): string
    {
        return sprintf(
            '%d checks · %d critical · %d errors · %d warnings · %d passed',
            $this->count(),
            $this->countBySeverity(CheckResult::CRITICAL),
            $this->countBySeverity(CheckResult::ERROR),
            $this->countBySeverity(CheckResult::WARNING),
            $this->countBySeverity(CheckResult::PASS),
        );
    }

    public function toJson(): string
    {
        return json_encode([
            'generated_at'    => now()->toIso8601String(),
            'summary'         => $this->summaryLine(),
            'worst_severity'  => $this->worstSeverity(),
            'results'         => array_map(
                fn (CheckResult $r) => $r->toArray(),
                $this->results
            ),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toMarkdown(): string
    {
        $lines = [];
        $lines[] = '# QA Report';
        $lines[] = '';
        $lines[] = 'Generated: ' . now()->toIso8601String();
        $lines[] = '';
        $lines[] = '> ' . $this->summaryLine();
        $lines[] = '';

        foreach ($this->bySuite() as $suite => $results) {
            $lines[] = '## ' . ucfirst($suite);
            $lines[] = '';
            $lines[] = '| Severity | Check | Message |';
            $lines[] = '| --- | --- | --- |';

            foreach ($results as $r) {
                $lines[] = sprintf(
                    '| %s | `%s` | %s |',
                    strtoupper($r->severity),
                    $r->check,
                    str_replace('|', '\\|', $r->message),
                );
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
