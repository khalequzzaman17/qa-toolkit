# Website Launch QA Toolkit for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/khalequzzaman17/qa-toolkit.svg?style=flat-square)](https://packagist.org/packages/khalequzzaman17/qa-toolkit)
[![Total Downloads](https://img.shields.io/packagist/dt/khalequzzaman17/qa-toolkit.svg?style=flat-square)](https://packagist.org/packages/khalequzzaman17/qa-toolkit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/khalequzzaman17/qa-toolkit/qa-toolkit.yml?branch=main&label=tests&style=flat-square)](https://github.com/khalequzzaman17/qa-toolkit/actions)
[![GitHub Code Style Status](https://img.shields.io/github/actions/workflow/status/khalequzzaman17/qa-toolkit/qa-toolkit.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/khalequzzaman17/qa-toolkit/actions)
[![PHP Version Required](https://img.shields.io/packagist/php-v/khalequzzaman17/qa-toolkit?style=flat-square)](https://packagist.org/packages/khalequzzaman17/qa-toolkit)
[![Laravel Version Required](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012-orange?style=flat-square)](https://www.laravel.com)
[![License](https://img.shields.io/packagist/l/khalequzzaman17/qa-toolkit?style=flat-square)](https://github.com/khalequzzaman17/qa-toolkit/blob/main/LICENSE)

A drop-in QA toolkit that gives your team a single command — `php artisan qa:run` — to validate that your Laravel application is ready to launch. The toolkit ships **120 checks** across four suites (33 Security, 22 Performance, 24 Functional, 41 Infra/Deploy), a handful of Artisan commands, ready-to-edit Pest test stubs, and CI pipeline definitions for GitHub Actions and GitLab CI.

Built for **mixed teams** (developers + QA engineers + PMs) — every check emits a human-readable label, a severity, and a fix-ready message. CI exits non-zero when the configured severity threshold is crossed, so the same command gates deploys.

> Author: **Khalequzzaman** · GitHub: [@khalequzzaman17](https://github.com/khalequzzaman17) · License: **MIT**

---

## Table of contents

1. [Quick start](#quick-start)
2. [How it works](#how-it-works)
3. [Available commands](#available-commands)
4. [Suite catalogs](#suite-catalogs)
   - [Security (~22 checks)](#security-suite)
   - [Performance (~20 checks)](#performance-suite)
   - [Functional (~24 checks)](#functional-suite)
   - [Infra / Deploy (~30 checks)](#infra--deploy-suite)
5. [Configuration](#configuration)
6. [CI pipelines](#ci-pipelines)
7. [Test stubs](#test-stubs)
8. [Extending — add your own checks](#extending)
9. [FAQ](#faq)

---

## Quick start

```bash
# 1. Install into your Laravel app
composer require khalequzzaman17/qa-toolkit

# 2. Publish the config file
php artisan vendor:publish --tag=qa-toolkit-config

# 3. Run all checks
php artisan qa:run

# 4. List every registered check
php artisan qa:checks
```

Or run a single suite:

```bash
php artisan qa:security
php artisan qa:performance
php artisan qa:functional
php artisan qa:infra
```

To generate a machine-readable report:

```bash
php artisan qa:report --format=json --path=storage/app/qa-report.json
```

---

## How it works

The toolkit is structured as a Laravel package. Every check is a small class extending `QaToolkit\Checks\Check`. Each one implements a single assertion, returns a `CheckResult`, and gets aggregated into a `Report`.

Severity levels (lowest → highest):

| Severity | Meaning                                                              | Example                                                |
| -------- | ------------------------------------------------------------------- | ------------------------------------------------------ |
| `pass`   | The check is satisfied.                                              | APP_KEY is set.                                        |
| `warning` | The check uncovered a smell that should be reviewed.              | A route without throttle middleware.                  |
| `error`  | A real issue that should be fixed before launch.                   | No `VerifyCsrfToken` in the web middleware group.     |
| `critical` | A launch blocker — fix immediately.                             | APP_DEBUG=true in production.                          |

`qa:run` exits `1` if any result matches or exceeds `qa-toolkit.fail_on`. Default is `warning` (so warnings, errors, and critical all fail the build in CI).

---

## Available commands

| Command           | Purpose                                                           |
| ----------------- | ---------------------------------------------------------------- |
| `qa:run`          | Run all enabled suites and print a per-check summary.           |
| `qa:security`     | Run only the security suite.                                     |
| `qa:performance`  | Run only the performance suite.                                  |
| `qa:functional`   | Run only the functional suite.                                   |
| `qa:infra`        | Run only the infra/deploy suite.                                 |
| `qa:report`       | Run all checks (optionally `--suite=`) and write a JSON/markdown report. |
| `qa:checks`       | List every registered check + its suite.                          |

Common flags:

```bash
php artisan qa:run --suite=security          # limit to one suite
php artisan qa:run --format=json              # output as JSON
php artisan qa:run --report=storage/app/qa.md # write a sidecar report
php artisan qa:run --stop-on-failure          # halt on first failure
```

---

## Suite catalogs

### Security suite

| ID (machine)                         | Label                                                                  | Severity when failed |
| ------------------------------------ | --------------------------------------------------------------------- | -------------------- |
| `app_key_set`                        | APP_KEY is set and 32+ chars                                           | critical             |
| `debug_mode`                         | APP_DEBUG is false in production                                       | critical             |
| `env_file_exposed`                   | .env is not web-accessible & not in version control                    | critical             |
| `csrf_protection`                    | VerifyCsrfToken middleware active on web routes                         | critical             |
| `mass_assignment`                    | No models use `guarded=[]` (mass assignment wide open)                  | error                |
| `auth_middleware_coverage`           | Non-public GET routes apply `auth`                                     | warning              |
| `http_security_headers`             | Security headers (HSTS, X-Frame, CSP) configured                       | warning              |
| `default_credentials`               | No default/weak DB credentials in production                           | critical             |
| `raw_user_input`                     | No raw user input echoed in Blade (XSS-prone)                           | error                |
| `throttling_middleware`              | Login/risky POST routes apply throttle middleware                       | warning              |
| `route_model_binding`               | Implicit `{model}` bindings use scope or auth gate                      | warning              |
| `filesystem_visibility`             | Public disk holds no sensitive files (.env, .sql, .zip)                | critical             |
| `cookie_settings`                    | Session cookies are HttpOnly, Secure, SameSite                         | error                |
| `bcrypt_cost`                        | Hasher bcrypt cost >= 10                                               | error                |
| `closure_routes`                     | No closure-based routes (covered by `route:cache`)                     | warning              |
| `debug_bar`                          | Debug bar / Telescope disabled in production                            | critical             |
| `open_redirect`                      | No `redirect()->to($input)` without a host allow-list                   | warning              |
| `sql_injection_sink`                | No `DB::raw($input)` or `whereRaw($input)` sinks                       | error                |
| `storage_link`                      | `storage:link` symlink does not expose app/ or framework/              | critical             |
| `method_override`                   | `_method` input is constrained to PUT/PATCH/DELETE                      | warning              |
| `honeypot_or_recaptcha`             | Public forms use honeypot or reCAPTCHA for spam                        | warning              |
| `env_key_rotation`                  | APP_KEY not committed in version control                               | critical             |
| `strict_mode`                        | MySQL strict mode enabled (STRICT_TRANS_TABLES)                         | warning              |
| `password_hash`                     | No password strings hard-coded in app/                                  | warning              |
| `composer_audit`                    | `composer audit` reports no advisories                                  | critical             |
| `gdpr_cookie`                       | Cookie consent banner rendered on first visit                           | warning              |
| `cors_config`                       | CORS paths/allowed_origins are explicit, not wildcard                  | critical             |
| `deprecated_hash`                   | No md5/sha1 password hashing or token signing in app/                  | error                |
| `eval`                               | No `eval()` / `assert()` / `create_function` in app/                   | critical             |
| `dd_leftover`                       | No `dd()`/`dump()`/`var_dump()` left in app/ source                     | warning              |
| `signed_routes`                     | Public action routes use signed middleware                              | warning              |
| `composer_lock_freshness`          | `composer.lock` exists and matches `composer.json`                       | critical             |
| `rate_limiting_routes`              | API routes apply `RateLimiter` (throttle:api)                           | warning              |

### Performance suite

| ID                       | Label                                                                  |
| ------------------------ | --------------------------------------------------------------------- |
| `cache_driver`           | CACHE_DRIVER is not array/file in production                           |
| `session_driver`         | SESSION_DRIVER is not file/cookie in production multi-server          |
| `queue_driver`           | QUEUE_CONNECTION is sync only in local/test                            |
| `lazy_loading_in_routes` | No N+1 patterns in controllers                                         |
| `model_strict_mode`      | Eloquent `shouldBeStrict()` enabled in production                     |
| `missing_index`          | Foreign-key columns have indexes                                       |
| `eager_loading`          | List queries use `with()` or `load()`                                  |
| `route_cache_configured` | Route cache + config cache present in production                       |
| `opcache_status`         | OPcache enabled in production (fpm)                                    |
| `migration_size`         | No migration file exceeds threshold                                    |
| `chatty_log`             | LOG_LEVEL is not debug/trace in production                             |
| `horizon_or_supervisor`  | Queue supervisor (Horizon/Supervisor) configured                        |
| `assets_compiled`        | Compiled Vite/Mix assets are present (manifest exists)                 |
| `chunked_queries`        | Large bulk queries use `chunk()`/`cursor()` not `->get()`             |
| `mail_driver`            | MAIL_MAILER is not log/smtp in production                               |
| `n_plus_one_in_view`     | No `model->relation` loops in Blade                                    |
| `schema_foreign_keys`    | Migration FK definitions are explicit (cascade rules)                  |
| `octane_or_swoole`       | High-throughput app uses Octane/Swoole (optional)                      |
| `uploaded_asset_cdn`     | Public uploads go to S3/CDN, not local public disk                     |
| `telescope_in_prod`      | Telescope is not enabled in production                                 |
| `query_log_disabled`     | DB query logging is off in production                                   |
| `schedule_cache`         | `schedule:cache` present (cache cron definitions)                       |

### Functional suite

| ID                          | Label                                                              |
| --------------------------- | ----------------------------------------------------------------- |
| `named_routes`              | All routes are named                                               |
| `controller_resolution`    | All routes resolve to an existing controller method               |
| `form_validation_coverage` | POST/PUT/DELETE routes apply `validate()` or FormRequest          |
| `http_verbs_coverage`      | Resource routes use all 7 verbs                                    |
| `model_scopes`             | Active models expose query scopes (active/published)              |
| `auth_routes_present`      | Login/logout/register/password reset routes exist                 |
| `mail_templates`           | Mail templates use Mailable classes, not raw `Mail::raw`           |
| `fallback_route`           | A fallback 404 route is defined                                   |
| `middleware_aliases`       | Custom middleware aliases referenced in routes exist              |
| `event_listeners`          | Event classes have listeners wired up                              |
| `jobs_have_tries`          | Job classes define `$tries` / `$backoff` for retries              |
| `scheduled_tasks`           | Schedule has at least one task defined (if applicable)             |
| `seeders_exist`            | Database has at least one seeder + DatabaseSeeder wires it        |
| `storage_dirs_writable`   | `storage/` + `bootstrap/cache/` are writable                       |
| `controller_dry_methods`  | No controller method is 50+ lines (extract to action)             |
| `blade_csrf`               | Every `<form>` in Blade includes `@csrf`                          |
| `uploads_validated`        | File upload routes validate mimes and size                         |
| `transaction_usage`        | Multi-write flows wrapped in `DB::transaction`                     |
| `route_conflict`           | No two routes register the same verb+URI                           |
| `error_views`              | Custom error views for 401/403/404/419/500                         |
| `mail_queue`               | Mailables implement `ShouldQueue`                                  |
| `notification_queue`       | Notifications implement `ShouldQueue`                              |
| `empty_routes`             | Application has at least one route registered                      |
| `routes_in_route_files`   | `routes/web.php` and `routes/api.php` are loaded                  |

### Infra / Deploy suite

| ID                            | Label                                                            |
| ----------------------------- | ---------------------------------------------------------------- |
| `required_env_keys`           | Required env keys (APP_ENV, DB, CACHE, QUEUE, MAIL...) are set   |
| `app_env_correct`             | APP_ENV is "production" in prod deploys                          |
| `migrations_up_to_date`      | All migrations are applied (no pending)                          |
| `cron_schedule_entry`        | System crontab includes `* * * * * ... schedule:run`           |
| `queue_worker_running`        | `queue:work` processes are running (production)                  |
| `backup_configured`           | Database backup strategy configured                              |
| `git_clean`                   | Git working tree is clean at deploy time                          |
| `maintenance_mode`            | App is NOT in maintenance mode                                    |
| `tls_enabled`                 | APP_URL uses `https://` in production                             |
| `db_connection`               | Database is reachable and reports correct version                 |
| `redis_reachable`             | Redis (cache/queue) is reachable when configured                  |
| `storage_public_symlink`     | `public/storage` symlink resolves correctly                       |
| `vendor_installed`            | `vendor/` directory exists and autoload is up-to-date            |
| `php_version`                 | PHP runtime version meets the framework requirement              |
| `required_php_extensions`    | Required PHP extensions are loaded                                |
| `sentry_or_bugsnag`           | Error monitoring SDK is configured (Sentry/Bugsnag)               |
| `storage_free_space`         | Storage volume has > 10% free disk space                         |
| `smtp_credentials`            | Mailer credentials are set (host/port/user/pass)                  |
| `failed_jobs_table`           | `failed_jobs` table exists and is queryable                       |
| `failed_jobs_backlog`         | No backlog of failed jobs (> 50 in last 7 days)                   |
| `swagger_or_open_api`         | API exposes `/docs` with OpenAPI spec (if API exists)             |
| `downtime_page`               | Maintenance mode renders a 503 view                               |
| `health_check_endpoint`       | A `/up` or `/health` endpoint exists for load balancer checks     |
| `charset_collation`           | MySQL charset is `utf8mb4` with `utf8mb4_unicode_ci`              |
| `log_level_configured`        | LOG_CHANNEL is daily (not single) in production                  |
| `web_server_config`           | Web server docroot points to `/public` (not `/`)                 |
| `asset_url_configured`        | ASSET_URL is set if assets are on a CDN/subdomain                 |
| `timezone_correct`            | `app.timezone` matches the business timezone                     |
| `memory_limit`                | php.ini `memory_limit` is reasonable (>= 128M)                    |
| `database_size`               | Largest table is < 50 GB (capacity signal)                        |
| `swaggered_if_api_controller` | API controllers apply API versioning if exposed                  |
| `trusted_proxies`             | `TrustProxies` middleware configured for the load balancer       |
| `horizon_configured`          | Horizon config published + dashboard gated in production          |
| `long_running_migrations`    | No migrations modify large tables without batching (heuristic)   |
| `connection_pool`             | Persistent DB connections are below the configured pool size     |
| `readme_exists`               | Project has README.md + .env.example                              |
| `asset_count`                 | Public assets total < 1000 files (housekeeping)                  |
| `ssl_cert_validity`           | SSL cert for APP_URL is valid for > 30 days                      |
| `backup_frequency`            | Backup cron is scheduled in Laravel scheduler                    |
| `horizon_supervisor`          | Horizon has supervisors configured with sensible sizes           |
| `obscure_env_keys`            | No empty/duplicate env keys in `.env`                            |

---

## Configuration

After publishing the config:

```bash
php artisan vendor:publish --tag=qa-toolkit-config
```

You can override everything in `config/qa-toolkit.php`:

```php
return [
    'enabled_suites' => [
        'security'    => true,
        'performance' => true,
        'functional'  => true,
        'infra'       => true,
    ],

    // What severity should cause qa:run to exit non-zero.
    // critical | error | warning | none
    'fail_on' => env('QA_FAIL_ON', 'warning'),

    // Output format: table | json | markdown | plain
    'format'   => env('QA_FORMAT', 'table'),

    'ignore' => [
        // Routes that legitimately bypass CSRF.
        'csrf_exempt_routes' => ['webhook/stripe'],

        // Models that intentionally allow mass assignment.
        'fillable_wildcard_models' => ['App\\Models\\Setting'],

        // Public GET routes that don't require auth.
        'unauthenticated_routes_allowlist' => [
            '/', 'login', 'register', 'password/*',
        ],
    ],

    'thresholds' => [
        'route_response_ms'   => 500,
        'queries_per_request' => 30,
        'migration_size_kb'  => 50,
    ],

    'infra' => [
        'required_env_keys' => [
            'APP_ENV', 'APP_KEY', 'APP_URL',
            'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE',
            'CACHE_DRIVER', 'QUEUE_CONNECTION', 'MAIL_MAILER', 'LOG_CHANNEL',
        ],
    ],
];
```

### Tuning CI behaviour

| Env var           | Effect                                                                 |
| ----------------- | --------------------------------------------------------------------- |
| `QA_FAIL_ON`      | `critical` (only critical issues fail CI) → `none` (never fail)         |
| `QA_FORMAT`       | `table` \| `json` \| `markdown`                                       |
| `QA_REPORT_PATH`  | Default file path used by `qa:report`                                  |
| `QA_SUITE_*`      | Set `QA_SUITE_SECURITY=false` etc. to disable a suite                   |
| `QA_THRESHOLD_*`  | Per-check thresholds, e.g. `QA_THRESHOLD_ROUTE_MS=300`                 |

---

## CI pipelines

Two ready-to-use pipelines are shipped in the `ci/` directory.

### GitHub Actions

Publish to `.github/workflows/qa-toolkit.yml`:

```bash
php artisan vendor:publish --tag=qa-toolkit-ci
```

The pipeline:
- Spins up MySQL + Redis services
- Installs PHP 8.3 with extensions and OPcache
- Runs `composer install`, Vite build, `migrate`, seeders
- Caches framework bootstrap (config/route/view/event)
- Runs Pest/PHPUnit with coverage (min 70%)
- Runs each QA suite in its own step, producing Markdown reports
- Uploads all reports as artifacts (30-day retention)
- Fails the build if `worst_severity` is `error` or `critical`

### GitLab CI

Copy `ci/gitlab-ci.yml` to your project's `.gitlab-ci.yml` (or include it locally):

```yaml
include:
  - local: '/vendor/khalequzzaman17/qa-toolkit/ci/gitlab-ci.yml'
```

Or, if you have mirrored the toolkit to your own GitLab namespace:

```yaml
include:
  - project: 'your-namespace/qa-toolkit'
    file: 'ci/gitlab-ci.yml'
    ref: 'main'
```

The pipeline:
- Uses `lorisleiva/laravel-docker` as the base image
- Runs four stages: `prepare` → `test` → `qa` → `deploy`
- Runs all four suites in parallel in the `qa` stage (with their own service containers)
- `qa:infra` runs `allow_failure: true` (many checks expect a live prod host)
- Generates a summary report and fails the pipeline if the worst severity is `error`+
- Production deploy is `manual` after the QA gate passes

---

## Test stubs

The toolkit ships **Pest test stubs** you can publish into your app:

```bash
php artisan vendor:publish --tag=qa-toolkit-stubs
# Writes to tests/QaToolkitStubs/
```

| Stub file                          | Coverage                                                            |
| ---------------------------------- | ------------------------------------------------------------------ |
| `QaRunTest.php`                    | Verifies the toolkit itself runs without crashing                  |
| `Feature/AuthTest.php`             | Login/logout, wrong password, rate-limit, email verification       |
| `Feature/FormValidationTest.php`   | Required fields, malformed email, short password, uniqueness       |
| `Feature/CrudTest.php`             | All 7 resource verbs + XSS prevention + auth gate                   |
| `Feature/FileUploadTest.php`      | Max size, disallowed extensions, disk storage, auth                |
| `Feature/MiddlewareCoverageTest.php` | Walks every auth'd GET route; asserts guest redirect             |
| `Feature/MailAndJobsTest.php`      | Welcome mail, queued notifications, job dispatch                    |
| `Feature/PublicRoutesTest.php`    | Smoke test public pages, 404, 419 CSRF, 405 wrong verb              |
| `Unit/ModelTest.php`               | Fillable, casts, hidden, scopes, belongsTo                          |

Customize them to match your routes and models, then commit. The CI pipeline runs them automatically.

---

## Extending

### Add your own check

1. Create a class extending `QaToolkit\Checks\Check`:

```php
namespace App\Qa\Checks\Security;

use QaToolkit\Checks\Check;

class CustomCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'Custom: my-specific concern'; }

    public function run()
    {
        if ($myCondition) {
            return $this->pass('Looks good.');
        }
        return $this->error('Fix this before launch.');
    }
}
```

2. Register it in your `AppServiceProvider::boot()`:

```php
public function boot(): void
{
    $this->app->extend('qa-toolkit.checks', function (array $checks) {
        $checks[\App\Qa\Checks\Security\CustomCheck::class] = app(\App\Qa\Checks\Security\CustomCheck::class);
        return $checks;
    });
}
```

That's it — `qa:run` will pick it up automatically.

### Project-specific overrides

Two hooks let you tune the toolkit per project without modifying it:

- **`config/qa-toolkit.php` → `ignore.*`** — tell the toolkit to ignore specific cases (exempt routes, allowed models, etc.)
- **`config/qa-toolkit.php` → `infra.*`** — list the env keys, queue workers, schedule entries, and backup disks your project requires

---

## FAQ

**Can I run the toolkit against a non-Laravel app?**
No — this is a Laravel-only package. The framework gives us container, config, routes, and DB abstractions.

**Will the toolkit modify my app?**
Never. All checks are read-only. The only writes are when you ask `qa:report` to write a file.

**Will the infra suite pass in CI?**
Some infra checks (cron, queue worker, SSL cert) expect a live production host. In CI they will warn or soft-fail — that's why the GitLab pipeline runs `qa:infra` with `allow_failure: true`. Run `qa:infra` on the production server itself for accurate results.

**Does the toolkit replace my test suite?**
No — it complements it. Run your Pest/PHPUnit suite first (functional correctness), then `qa:run` (launch readiness), then deploy.

**Why are there so many warnings?**
Each check returns a warning when its concern isn't *perfectly* addressed but isn't a launch blocker. Tune the `fail_on` threshold so CI doesn't gate on warnings — or fix the underlying issue. Both are valid.

---

## License

The **Website Launch QA Toolkit for Laravel** is open-sourced software licensed under the **MIT License**. Please see the [LICENSE](LICENSE) file for the full text.

## Author

**Khalequzzaman** — [github.com/khalequzzaman17](https://github.com/khalequzzaman17)

Maintained as an open-source project. Contributions, bug reports, and feature requests are welcome on the [GitHub repository](https://github.com/khalequzzaman17/qa-toolkit).

## Credits

- Built on top of the [Laravel](https://laravel.com) framework.
- Inspired by Spatie's [Laravel-specific security and audit packages](https://github.com/spatie) and the wider Laravel community's checklists for pre-launch readiness.
- Pest test stubs use the [Pest PHP](https://pestphp.com) testing framework.
- CI pipelines reference Laravel Docker images by [lorisleiva](https://github.com/lorisleiva/laravel-docker).

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for what changed in each version.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for the local-dev setup, branching strategy, and PR checklist.
