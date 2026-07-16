# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.1.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security issue, please email **mohamed@hekal.dev** rather than opening a public issue.

Include:

- Affected version
- Steps to reproduce
- Impact assessment

We aim to acknowledge reports within 48 hours.

## Recommendations

- Rotate `CLASSBRIDGE_TOKEN_KEY` and `CLASSBRIDGE_WEBHOOK_SECRET` regularly
- Keep join tokens short-lived and one-time in production
- Validate webhook signatures before processing provider events
- Scope API routes with your application's authentication middleware
