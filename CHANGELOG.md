# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Pending
- Tag the first release (`v1.0.0`) once CI pipeline is green on `main`.

---

## [1.0.0] - 2026-10-22

### Added
- Initial release of the Website Launch QA Toolkit for Laravel.
- **120 checks** across four suites:
  - 33 **Security** checks (APP_KEY, debug mode, CSRF, mass assignment, headers, auth coverage, eval/dd detection, CORS, composer audit, GDPR cookie banner, etc.)
  - 22 **Performance** checks (cache driver, session driver, queue driver, N+1 detection, missing indexes, OPcache, framework caches, Octane, schedule cache, etc.)
  - 24 **Functional** checks (named routes, controller resolution, form validation coverage, HTTP verbs, model scopes, auth routes, mail templates, event listeners, seeders, Blade CSRF, upload validation, transaction usage, error views, etc.)
  - 41 **Infra/Deploy** checks (env keys, APP_ENV, migrations, cron, queue workers, backups, git clean, maintenance mode, TLS, DB connection, Redis, PHP extensions, error monitoring, free space, SMTP credentials, failed jobs, OpenAPI, health endpoint, charset/collation, trusted proxies, SSL cert validity, etc.)
- 7 **Artisan commands**:
  - `qa:run` — one-shot pre-launch sweep across all enabled suites.
  - `qa:security`, `qa:performance`, `qa:functional`, `qa:infra` — run a single suite.
  - `qa:report` — JSON or Markdown report for CI artifact upload.
  - `qa:checks` — list every registered check and its suite.
- 4 severity levels: `pass`, `warning`, `error`, `critical`.
- Configurable `fail_on` threshold so CI gates only on what matters.
- 9 **Pest test stubs** published via `vendor:publish --tag=qa-toolkit-stubs`:
  - `AuthTest.php`, `FormValidationTest.php`, `CrudTest.php`, `FileUploadTest.php`,
    `MiddlewareCoverageTest.php`, `MailAndJobsTest.php`, `PublicRoutesTest.php`,
    `ModelTest.php`, `QaRunTest.php`.
- **CI pipeline** definitions for both GitHub Actions and GitLab CI:
  - Spins up MySQL + Redis services.
  - Runs Pest/PHPUnit with coverage gate at 70%.
  - Runs each QA suite as its own step.
  - Uploads Markdown/JSON reports as build artifacts.
  - Manual production deploy gate after QA passes.
- Config file with ignore-allowlists, thresholds, and infra expectations.
- `install.sh` helper for one-step install into an existing Laravel app.
- Full README documenting every check, configuration options, and CI integration.
- MIT License with author attribution to Khalequzzaman.

### Supported Versions
- PHP 8.1, 8.2, 8.3, 8.4
- Laravel 10.x, 11.x, 12.x

---

[Unreleased]: https://github.com/khalequzzaman17/qa-toolkit/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/khalequzzaman17/qa-toolkit/releases/tag/v1.0.0
