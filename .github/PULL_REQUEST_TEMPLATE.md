# Thanks for the PR!

A quick checklist before we merge — please cross off whatever doesn't apply.

## The change

- [ ] This PR addresses one issue or adds one check.
- [ ] Commit messages follow Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`).
- [ ] No `dd()`, `var_dump()`, or commented-out code in the diff.

## If you added/changed a check

- [ ] The check class lives in the right suite folder (`src/Checks/{Security,Performance,Functional,Infra}/`).
- [ ] The check extends `QaToolkit\Checks\Check` and implements `suite()` + `label()` + `run()`.
- [ ] The check returns one of `pass` / `warning` / `error` / `critical` — never throws for expected failures.
- [ ] The check is registered in `QaToolkitServiceProvider::CHECKS`.
- [ ] The check is added to the appropriate suite catalog table in `README.md`.
- [ ] A test stub covers the check in `tests/`.

## If you changed the config

- [ ] New config keys have sensible defaults in `config/qa-toolkit.php`.
- [ ] Backward compatibility is preserved (existing installs should keep working).

## CI hygiene

- [ ] `composer test` (or `vendor/bin/pest`) passes locally.
- [ ] No new dependencies introduced without justification.

## Description

<!-- One paragraph explaining the why. -->

## Related issues

<!-- e.g. Closes #12 -->
