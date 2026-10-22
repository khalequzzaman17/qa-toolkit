# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in the QA Toolkit for Laravel, please report it responsibly — **do not open a public issue**.

- Email: open an issue labeled `security` on GitHub only if you cannot reach me privately, otherwise please use the contact form on my [GitHub profile](https://github.com/khalequzzaman17).
- Include: affected version, the check ID or class affected, the smallest reproduction case, and the impact.

You will receive a response within 72 hours. If the issue is accepted, a fix will be released as a patch version within 7 days.

## Scope

This security policy covers the **toolkit itself** — its source code in this repository. It does **not** cover the applications that use the toolkit. The toolkit is a static-analyser-style helper; running it does not grant the toolkit access to your production data.

## Out of Scope

- Vulnerabilities in third-party packages that the toolkit depends on (please report to upstream maintainers).
- Issues flagged by the toolkit itself (those are documented in the README, not security vulnerabilities in the toolkit).
