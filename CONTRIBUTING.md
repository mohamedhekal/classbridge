# Contributing

Thank you for contributing to ClassBridge.

## Development setup

```bash
git clone https://github.com/mohamedhekal/classbridge.git
cd classbridge
composer update
vendor/bin/pest
```

## Code style

- PHP 8.2+ with `declare(strict_types=1);`
- Laravel Pint (`composer format`)
- PHPStan level 6 (`composer analyse`)

## Pull requests

1. Fork the repository
2. Create a feature branch
3. Add or update tests for behavioral changes
4. Ensure CI passes (Pest, Pint, PHPStan)
5. Open a PR with a clear description and test plan

## Commit messages

Use concise, imperative subjects focused on *why* the change matters.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
