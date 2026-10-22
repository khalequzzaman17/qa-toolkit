---
name: Feature request
about: Suggest a new check or a behaviour change
title: 'feat: '
labels: enhancement, needs-triage
assignees: khalequzzaman17
---

## What's the use case?

<!-- A real pre-launch situation where the existing toolkit doesn't help. Concretely: "When deploying a Laravel app that uses X, I want check Y so I don't ship bug Z." -->

## Proposed solution

<!-- A short description of the new check or behaviour. If you can sketch the assertion in pseudo-code, even better. -->

```php
// pseudo-code sketch of the proposed check
if ($something) {
    return $this->warning('...');
}
```

## Severity recommendation

- [ ] Critical — launch blocker
- [ ] Error — should fix before launch
- [ ] Warning — review-worthy smell
- [ ] Pass — informational only

## Suite

- [ ] Security
- [ ] Performance
- [ ] Functional
- [ ] Infra / Deploy

## Alternatives considered

<!-- What other approaches have you tried, and why didn't they work? -->

## Out of scope

<!-- What should this check NOT do, to stay narrow? -->
