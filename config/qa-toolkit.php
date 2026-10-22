<?php

return [

    /*
    |--------------------------------------------------------------------------
    | QA Toolkit Configuration
    |--------------------------------------------------------------------------
    |
    | The following values tune which check suites are run by `php artisan qa:run`
    | and how their output is rendered. Override per-environment by replicating
    | the relevant keys in config/qa-toolkit.local.php or via env vars.
    |
    */

    'enabled_suites' => [
        'security'   => env('QA_SUITE_SECURITY', true),
        'performance'=> env('QA_SUITE_PERFORMANCE', true),
        'functional' => env('QA_SUITE_FUNCTIONAL', true),
        'infra'      => env('QA_SUITE_INFRA', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity gating
    |--------------------------------------------------------------------------
    |
    | 'fail_on' controls the minimum severity that will cause `qa:run` to exit
    | non-zero — used by CI to gate deploys. 'critical' = only critical issues
    | fail the build; 'warning' lets warnings through but fails on errors.
    |
    */

    'fail_on' => env('QA_FAIL_ON', 'warning'), // critical | warning | error | none

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    |
    | 'format' accepts: table, json, markdown, plain.
    | 'output_path' is used when `--report` is passed; if null, falls back to
    | storage_path('app/qa-report.{ext}').
    |
    */

    'format'     => env('QA_FORMAT', 'table'),
    'output_path'=> env('QA_REPORT_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | Project-specific allow-lists
    |--------------------------------------------------------------------------
    |
    | Tell the toolkit about expected/known exceptions so they don't fire as
    | false positives. Each suite exposes its own ignore keys.
    |
    */

    'ignore' => [

        // Routes that legitimately bypass CSRF (e.g. webhook receivers).
        'csrf_exempt_routes' => [
            // 'webhook/stripe',
        ],

        // Models that intentionally allow mass assignment (be cautious).
        'fillable_wildcard_models' => [
            // 'App\\Models\\Setting',
        ],

        // Env keys whose presence in version control is expected (e.g. CI secrets).
        'env_safe_keys' => [
            // 'CI_SEED_TOKEN',
        ],

        // Routes without auth on purpose (marketing pages, login, etc.).
        'unauthenticated_routes_allowlist' => [
            '/',
            'login',
            'register',
            'password/*',
            'forgot-password',
            'reset-password/*',
        ],

        // Tables that are intentionally without foreign keys (e.g. partitioned).
        'no_foreign_key_tables' => [
            // 'jobs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance thresholds
    |--------------------------------------------------------------------------
    |
    | The performance suite measures a handful of quantitative signals against
    | these thresholds and fails if a value crosses the wrong direction.
    |
    */

    'thresholds' => [

        // Routes that take longer than this (ms) on a warm cache fail.
        'route_response_ms'        => env('QA_THRESHOLD_ROUTE_MS', 500),

        // Queries per request — anything above this triggers a warning.
        'queries_per_request'       => env('QA_THRESHOLD_QUERIES', 30),

        // Eloquent models bootstrapped by the framework baseline.
        'baseline_memory_mb'        => env('QA_THRESHOLD_MEMORY_MB', 64),

        // Largest acceptable migration file (KB) — refactor big migrations.
        'migration_size_kb'         => env('QA_THRESHOLD_MIGRATION_KB', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Infra / deploy expectations
    |--------------------------------------------------------------------------
    |
    | Use these to enforce environment-specific expectations. Anything set here
    | is checked at runtime by the infra suite.
    |
    */

    'infra' => [

        // Required env keys in production.
        'required_env_keys' => [
            'APP_ENV',
            'APP_KEY',
            'APP_URL',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_DATABASE',
            'CACHE_DRIVER',
            'QUEUE_CONNECTION',
            'MAIL_MAILER',
            'LOG_CHANNEL',
        ],

        // Queue workers that must be running in production.
        'required_queue_workers' => [
            // 'default',
        ],

        // Scheduled jobs that must exist in production.
        'required_schedule_entries' => [
            // 'schedule:run',
        ],

        // Required backed-up disks (must have a backup strategy configured).
        'required_backup_disks' => [
            // 'local',
            // 's3',
        ],
    ],
];
