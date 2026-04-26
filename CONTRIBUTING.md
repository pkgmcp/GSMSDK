# Contributing to GSMSDK

We welcome contributions! Here's how you can help.

## Code of Conduct

Be respectful and inclusive. Follow our [Code of Conduct](CODE_OF_CONDUCT.md).

## Getting Started

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/amazing-feature`
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass: `composer test`
6. Commit your changes: `git commit -m 'Add amazing feature'`
7. Push to the branch: `git push origin feat/amazing-feature`
8. Open a Pull Request

## Development Guidelines

- Use PHP 8.5+ features (readonly, enums, etc.)
- Follow PSR-12 coding standards
- Add type hints for all functions
- Write tests for new code
- Update documentation for new features
- Keep BC breaks to a minimum

## Running Tests

```bash
composer install
composer test
composer coverage
```

## Reporting Issues

Please use the [GitHub Issue Tracker](https://github.com/pkgmcp/gsmsdk/issues).

## Feature Requests

Open an issue with:
- Clear description of the feature
- Use cases and examples
- Proposed API (if applicable)

## Pull Request Process

1. Update tests and documentation
2. Ensure CI passes
3. Reference related issues
4. Be prepared to address feedback

Thank you for contributing!
