<?php

namespace QaToolkit\Support;

use Illuminate\Support\Arr;

/**
 * Thin wrapper around config('qa-toolkit') with typed accessors.
 *
 * Keeps checks readable: `Cfg::ignore('csrf_exempt_routes')` instead of
 * the noisier `config('qa-toolkit.ignore.csrf_exempt_routes', [])`.
 */
final class Cfg
{
    public static function enabled(string $suite): bool
    {
        return (bool) config('qa-toolkit.enabled_suites.' . $suite, true);
    }

    public static function failOn(): string
    {
        return (string) config('qa-toolkit.fail_on', 'warning');
    }

    public static function format(): string
    {
        return (string) config('qa-toolkit.format', 'table');
    }

    public static function outputPath(): ?string
    {
        $path = config('qa-toolkit.output_path');
        return $path ? (string) $path : null;
    }

    /**
     * Fetch a key from the `ignore` block, always as an array.
     *
     * @return array<int|string, mixed>
     */
    public static function ignore(string $key): array
    {
        $value = config('qa-toolkit.ignore.' . $key, []);
        return Arr::wrap($value);
    }

    /**
     * Fetch a threshold value (numeric).
     */
    public static function threshold(string $key): float
    {
        return (float) config('qa-toolkit.thresholds.' . $key, 0);
    }

    /**
     * Fetch an infra expectation, always as an array.
     *
     * @return array<int|string, mixed>
     */
    public static function infra(string $key): array
    {
        $value = config('qa-toolkit.infra.' . $key, []);
        return Arr::wrap($value);
    }
}
