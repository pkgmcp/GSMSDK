# TOOLS.md - Development Tools

## Environment

- **OS**: Debian Bookworm (slim) or compatible
- **PHP**: 8.5+
- **Composer**: 2.0+
- **Extensions**: PDO, JSON, Mbstring
- **Database**: MySQL 8.0+, PostgreSQL 14+, SQLite 3.35+

## Code Standards

### PSR-12 Compliance

All code follows [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards:

```bash
# Check with PHP CS Fixer (if installed)
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

### Key Rules

- `declare(strict_types=1)` at top of all PHP files
- 4 spaces for indentation (no tabs)
- Unix line endings (LF)
- Files end with single newline
- Opening `<?php` tag on its own line
- No closing `?>` tag in pure PHP files
- One statement per line
- Braces on same line for control structures
- Visibility declarations on all methods/properties
- Type declarations for all parameters and returns
- Nullable types use `?Type` syntax
- `readonly` for immutable value objects
- `#[Override]` attribute for overridden methods

### Naming Conventions

- **Classes**: `StudlyCaps`
- **Methods**: `camelCase`
- **Properties**: `camelCase`
- **Constants**: `UPPER_CASE`
- **Namespaces**: `PascalCase\SubNamespace`
- **Files**: `StudlyCaps.php` for classes, `lowercase.php` for others

### Type Safety

```php
// Good
public function find(int $id): ?User
{
    // ...
}

// Avoid
public function find($id)
{
    // ...
}
```

## Project Structure

```
gsmsdk/
├── src/
│   ├── Core/              # Application core
│   ├── HTTP/              # HTTP layer
│   ├── Database/          # Database layer
│   ├── CLI/               # Console interface
│   ├── Desktop/           # Desktop support
│   ├── Mobile/            # Mobile support
│   ├── Fastboot/          # Fastboot integration
│   ├── ADB/               # ADB integration
│   ├── Core/Engine/       # Templating engine
│   ├── Contracts/         # Interfaces
│   ├── Traits/            # Reusable traits
│   └── Exceptions/        # Exception classes
├── app/
│   ├── Controllers/       # Application controllers
│   ├── Models/            # Application models
│   └── Views/             # View templates (.gsm.php)
├── resources/
│   ├── views/             # View templates
│   │   ├── layouts/       # Layout templates
│   │   ├── partials/      # Partial templates
│   │   └── components/    # Reusable components
│   └── assets/            # Static assets
├── tests/
│   ├── Unit/              # Unit tests
│   └── Feature/           # Feature tests
├── public/                # Web root
│   ├── index.php          # Front controller
│   └── views/             # Public views
├── config/                # Configuration files
├── storage/               # Runtime files
└── vendor/               # Dependencies
```

## Git Workflow

### Branch Strategy

- `main` - Production-ready code
- `develop` - Development branch (latest features)
- `feature/*` - Feature branches
- `hotfix/*` - Emergency fixes

### Commit Messages

```
feat: add new feature
test: add tests for feature
fix: resolve bug
docs: update documentation
style: code formatting (no logic change)
refactor: code refactoring
perf: performance improvement
chore: maintenance tasks
```

### Git Hooks

Pre-commit hooks run:
- PHP syntax check
- PSR-12 validation (if configured)
- Basic static analysis

## Testing

### PHPUnit

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run specific test class
./vendor/bin/phpunit tests/Unit/Engine/GsmTest.php

# Run with coverage
composer coverage
```

### Test Structure

```
tests/
├── Unit/
│   ├── Engine/
│   │   └── GsmTest.php      # GSM engine tests
│   └── MvcApplicationTest.php
└── Feature/
    └── ExampleTest.php
```

### Writing Tests

```php
<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit;

use GSMSDK\Core\Application;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application([...]);
    }

    public function testSomething(): void
    {
        $result = $this->app->doSomething();
        $this->assertSame('expected', $result);
    }
}
```

## Development Tools

### VS Code Settings

`.vscode/settings.json`:

```json
{
    "php.validate.executablePath": "/usr/bin/php",
    "php-cs-fixer.executablePath": "./vendor/bin/php-cs-fixer",
    "editor.formatOnSave": true,
    "editor.defaultFormatter": "junstyle.php-cs-fixer"
}
```

### PHPStan

Static analysis configuration in `phpstan.neon`:

```neon
parameters:
    level: 8
    paths:
        - src
        - tests
```

## Continuous Integration

GitHub Actions workflow:

```yaml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
      - run: composer install
      - run: composer test
      - run: composer coverage
```

## Performance

### Optimization Tips

1. **OPCache**: Enable PHP OPcache
2. **Autoloader**: Use Composer's optimized autoloader
3. **Template Cache**: GSM engine compiles and caches templates
4. **Database**: Use prepared statements and connection pooling
5. **Static Files**: Serve via CDN or web server

### Benchmarking

```php
$start = microtime(true);
// Code to benchmark
$elapsed = microtime(true) - $start;
echo "Elapsed: {$elapsed}s\n";
```

## Debugging

### Error Reporting

```php
// Development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');
```

### Logging

```php
// Application logs to storage/logs/error.log
// Configure Monolog for advanced logging
```

## Deployment

### Checklist

- [ ] Tests passing
- [ ] Code coverage adequate
- [ ] Static analysis clean
- [ ] Configuration updated for production
- [ ] Database migrations run
- [ ] Assets compiled/minified
- [ ] OPcache reset
- [ ] Health checks configured

### Environment Variables

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_DATABASE=app
DB_USERNAME=user
DB_PASSWORD=secret
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Class not found | `composer dump-autoload` |
| Template not found | Check view path configuration |
| Database error | Verify connection settings |
| Permission denied | Check storage/ directory permissions |
| Out of memory | Increase PHP memory_limit |

## Resources

- [PHP Documentation](https://www.php.net/manual/)
- [PSR Standards](https://www.php-fig.org/psr/)
- [Composer](https://getcomposer.org/)
- [PHPUnit](https://phpunit.de/)
- [PHP-CS-Fixer](https://cs.symfony.com/)
- [PHPStan](https://phpstan.org/)
