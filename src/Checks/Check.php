<?php

namespace QaToolkit\Checks;

use QaToolkit\Support\CheckResult;

/**
 * Base contract every check must satisfy.
 *
 * A Check is a single, atomic assertion about the application's launch
 * readiness. It receives the Laravel container (so it can resolve config,
 * routes, DB, filesystem) and returns a CheckResult describing the outcome.
 *
 * Convention: one Check = one concern. Don't bundle multiple assertions
 * into a single check; if a check needs to verify several things, split it.
 */
abstract class Check
{
    /**
     * Human-readable label shown in the report. Should be short and action-
     * oriented, e.g. "App key set", "No wildcard fillable on User".
     */
    abstract public function label(): string;

    /**
     * Short machine identifier, stable across runs. Used as the JSON key
     * in reports so you can diff two runs.
     */
    public function id(): string
    {
        $parts = explode('\\', static::class);
        return (string) str(array_pop($parts))->snake();
    }

    /**
     * The suite this check belongs to: security | performance | functional | infra.
     */
    abstract public function suite(): string;

    /**
     * Run the check. Must return a CheckResult — never throw for "expected"
     * failures (e.g. config missing); only throw for genuine tool errors.
     */
    abstract public function run(): CheckResult;

    /**
     * Convenience helpers for the most common outcomes.
     */
    protected function pass(string $message = '', array $context = []): CheckResult
    {
        return CheckResult::pass($this->suite(), $this->id(), $message, $context);
    }

    protected function warning(string $message, array $context = []): CheckResult
    {
        return CheckResult::warning($this->suite(), $this->id(), $message, $context);
    }

    protected function error(string $message, array $context = []): CheckResult
    {
        return CheckResult::error($this->suite(), $this->id(), $message, $context);
    }

    protected function critical(string $message, array $context = []): CheckResult
    {
        return CheckResult::critical($this->suite(), $this->id(), $message, $context);
    }
}
