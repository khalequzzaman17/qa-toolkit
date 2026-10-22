<?php

namespace QaToolkit\Support;

/**
 * The outcome of running a single check.
 *
 * Each Check returns one CheckResult. The Report aggregates CheckResults
 * into a per-suite breakdown and an overall pass/fail decision.
 */
final class CheckResult
{
    public const PASS    = 'pass';
    public const WARNING = 'warning';
    public const ERROR  = 'error';
    public const CRITICAL = 'critical';

    public string $severity;
    public string $suite;
    public string $check;
    public string $message;
    public array $context;

    public function __construct(
        string $suite,
        string $check,
        string $message,
        string $severity = self::PASS,
        array $context = []
    ) {
        $this->suite    = $suite;
        $this->check    = $check;
        $this->message  = $message;
        $this->severity = $severity;
        $this->context  = $context;
    }

    public static function pass(string $suite, string $check, string $message = '', array $context = []): self
    {
        return new self($suite, $check, $message ?: 'OK', self::PASS, $context);
    }

    public static function warning(string $suite, string $check, string $message, array $context = []): self
    {
        return new self($suite, $check, $message, self::WARNING, $context);
    }

    public static function error(string $suite, string $check, string $message, array $context = []): self
    {
        return new self($suite, $check, $message, self::ERROR, $context);
    }

    public static function critical(string $suite, string $check, string $message, array $context = []): self
    {
        return new self($suite, $check, $message, self::CRITICAL, $context);
    }

    public function isPass(): bool
    {
        return $this->severity === self::PASS;
    }

    public function isFailure(): bool
    {
        return in_array($this->severity, [self::WARNING, self::ERROR, self::CRITICAL], true);
    }

    /**
     * A result's weight, used to compute the worst-seen severity for a suite.
     * Higher = more severe.
     */
    public function weight(): int
    {
        return match ($this->severity) {
            self::PASS     => 0,
            self::WARNING  => 1,
            self::ERROR    => 2,
            self::CRITICAL => 3,
            default        => 0,
        };
    }

    public function toArray(): array
    {
        return [
            'suite'    => $this->suite,
            'check'    => $this->check,
            'severity' => $this->severity,
            'message'  => $this->message,
            'context'  => $this->context,
        ];
    }
}
