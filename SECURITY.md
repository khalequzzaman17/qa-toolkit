# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability within the QA Toolkit for Laravel, please report it responsibly and privately — **please do NOT open a public GitHub issue**.

### How to Report

Please report security vulnerabilities through either of these private channels:
1. **Email**: Send details directly to [khalequzzaman17@gmail.com](mailto:khalequzzaman17@gmail.com) with the subject `[Security] QA Toolkit Vulnerability`.
2. **GitHub Security Advisories**: Submit privately via [GitHub Security Advisories](https://github.com/khalequzzaman17/qa-toolkit/security/advisories/new).

Please include in your report:
- The affected package version
- The check ID, class, or command affected
- Steps to reproduce or a minimal reproduction case
- An explanation of the potential impact

You will receive an acknowledgement within 48 hours. If confirmed, a security patch will be tagged and released promptly.

## General Bug Reports & Feature Requests

For standard bugs, questions, and feature requests that do not expose a security vulnerability, please open an issue on [GitHub Issues](https://github.com/khalequzzaman17/qa-toolkit/issues).

## Scope

This security policy covers the **toolkit itself** — its source code in this repository. It does **not** cover applications that use the toolkit. The toolkit performs read-only inspection and static checks; running it does not grant the toolkit access to modify your production data.

## Out of Scope

- Vulnerabilities in upstream third-party dependencies (please report to upstream maintainers).
- Issues and smells in your application flagged by the toolkit during QA sweeps (those are application-level issues, not vulnerabilities in the toolkit itself).
