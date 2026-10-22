# Contributing to QA Toolkit for Laravel

Thanks for being interested in contributing! This is a short guide to get you up and running.

## Project structure

```
src/
├── Checks/
│   ├── Security/        # 33 security checks (auth, CSRF, headers, secrets, ...)
│   ├── Performance/     # 22 perf checks (N+1, cache, queue, indexes, ...)
│   ├── Functional/      # 24 functional checks (routes, controllers, validation, ...)
│   └── Infra/           # 41 infra/deploy checks (env, migrations, cron, backups, ...)
├── Console/Commands/    # 7 Artisan commands (qa:run, qa:security, qa:report, ...)
├── Reports/             # Report aggregation (table/JSON/markdown)
└── Support/             # Cfg + CheckResult (severity enum)

tests/stubs/             # Pest test stubs published into user apps
ci/                      # GitHub Actions + GitLab CI YAMLs
config/                  # Published config file
```

Each check is a small, atomic class extending `QaToolkit\Checks\Check`. One check = one concern. Never bundle multiple assertions into a single check.

## Local development

Requirements: PHP 8.1+, Composer, MySQL/SQLite, Redis (optional).

```bash
# 1. Fork & clone
git clone https://github.com/khalequzzaman17/qa-toolkit.git
cd qa-toolkit

# 2. Install dependencies
composer install

# 3. Verify the toolkit runs in isolation
vendor/bin/pest

# 4. (Optional) Test against a real Laravel app
cd ../my-laravel-app
composer config repositories.toolkit path /path/to/qa-toolkit
composer require khalequzzaman17/qa-toolkit:dev-main
php artisan vendor:publish --tag=qa-toolkit-config
php artisan qa:run
```

## Adding a new check

1. Pick the right suite folder (`Security/`, `Performance/`, `Functional/`, or `Infra/`).
2. Create a class extending `QaToolkit\Checks\Check`:

```php
namespace QaToolkit\Checks\Security;

use QaToolkit\Checks\Check;

class MyNewConcernCheck extends Check
{
    public function suite(): string { return 'security'; }
    public function label(): string { return 'One-line description'; }

    public function run()
    {
        if ($ok) {
            return $this->pass('All good.');
        }

        return $this->warning('Describe what is wrong and how to fix it.');
    }
}
```

3. Register the class in `QaToolkitServiceProvider::CHECKS`.
4. Add an entry to the suite catalog table in `README.md`.
5. Add a test that exercises the check (look at `tests/stubs/QaRunTest.php`).
6. Open a PR with the prefix `feat(checks):` and a description of the use case.

## Severity guidelines

| Severity | When to use                                                     | Example                                                |
| -------- | ---------------------------------------------------------------- | ------------------------------------------------------ |
| `pass`   | The check is satisfied.                                          | APP_KEY set.                                            |
| `warning` | A smell that should be reviewed but is not a launch blocker.    | Missing throttle on a login route.                    |
| `error`  | A real issue that should be fixed before launch.                | No `VerifyCsrfToken` in web middleware group.         |
| `critical` | A launch blocker — fix immediately.                            | APP_DEBUG=true in production.                          |

**Heuristic**: if you'd be uncomfortable shipping with this issue in production, it is at least `error`. If it would make you page someone at 3am, it is `critical`.

## Branching & PR

- Fork from `main`.
- Branch name: `feat/...`, `fix/...`, `docs/...`, `chore/...`.
- One logical change per PR.
- Run `vendor/bin/pest` before pushing.
- Use Conventional Commits in your commit messages.
- Be kind in code review.

## Code style

- PSR-12 + Laravel's relaxed style (line length ~120, return type on next line is fine).
- Prefer `match()` over long `switch` statements.
- Never use `dd()` or `dump()` in merged code.
- Always type-hint arguments and return types.
- Prefer Readonly properties and `final` classes where the intent is "this is sealed".

## Reporting bugs

Open an [issue](https://github.com/khalequzzaman17/qa-toolkit/issues/new?template=bug_report.md) with:

- The exact check ID (from `php artisan qa:checks`)
- The output of the failing check
- Your Laravel version, PHP version, and operating system
- The smallest reproduction case you can write

## Feature requests

Open an issue with the `feature_request` template. Be specific about the use case — we prefer checks that solve real launch pain over hypothetical ones.

## License

By contributing you agree that your contributions will be licensed under the MIT License.
