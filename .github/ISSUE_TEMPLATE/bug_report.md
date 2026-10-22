---
name: Bug report
about: Something is wrong with a check or command
title: 'bug: '
labels: bug, needs-triage
assignees: khalequzzaman17
---

## Which check is misbehaving?

<!-- Run `php artisan qa:checks` and paste the check ID (e.g. `app_key_set`) here. If unsure, paste the check label. -->

## Expected behaviour

<!-- What did you expect `php artisan qa:*` to report? -->

## Actual behaviour

<!-- What did it actually report? Paste the exact command + output. -->

```bash
$ php artisan qa:run
# paste output here
```

## Environment

- QA Toolkit version: `<!-- output of `composer show khalequzzaman17/qa-toolkit-laravel | grep versions` -->`
- Laravel version: `<!-- php artisan --version -->`
- PHP version: `<!-- php --version -->`
- Database driver + version: `<!-- e.g. MySQL 8.0.34 / PostgreSQL 16 / SQLite 3.45 -->`
- OS: `<!-- e.g. Ubuntu 22.04 on WSL2 -->`
- Cache driver: `<!-- e.g. redis / file / array -->`
- Queue driver: `<!-- e.g. redis / sync / database -->`

## Smallest reproduction case

```bash
# 1. Fresh Laravel install
# 2. Install the toolkit
# 3. Trigger the check
# ... paste here
```

## Additional context

<!-- Anything else that helps — logs, related issues, fixes you've tried, etc. -->
