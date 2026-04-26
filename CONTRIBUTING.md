# Contributing to GSMSDK

We welcome contributions! Here's how you can help make GSMSDK better.

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers
- Focus on constructive feedback
- Follow our [Security Policy](SECURITY.md)

## Getting Started

### Setting Up Development Environment

```bash
# Clone repository
git clone https://github.com/pkgmcp/GSMSDK.git
cd GSMSDK

# Install dependencies
composer install

# Run tests
composer test

# Check code style
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

### Repository Structure

- `src/` - Source code (PSR-4 autoloaded)
- `tests/` - Test suite
- `public/` - Web entry point
- `resources/views/` - View templates (.gsm.php)
- `examples/` - Example applications
- `docs/` - Documentation

## Development Workflow

### 1. Fork and Clone

```bash
git clone https://github.com/YOUR_USERNAME/GSMSDK.git
cd GSMSDK
git remote add upstream https://github.com/pkgmcp/GSMSDK.git
```

### 2. Create Feature Branch

```bash
git checkout -b feat/descriptive-feature-name
```

### 3. Make Changes

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Keep changes focused

### 4. Run Tests

```bash
composer test
```

All tests must pass.

### 5. Check Code Style

```bash
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

All files must comply.

### 6. Commit Changes

```bash
git add .
git commit -m "feat: describe your change"
git push origin feat/descriptive-feature-name
```

### 7. Create Pull Request

- Target: `develop` branch
- Include description of changes
- Reference related issues
- Link to tests

## Contribution Types

### Bug Reports

1. Check if issue already exists
2. Create new issue with:
   - Clear title
   - Description
   - Steps to reproduce
   - Expected behavior
   - Actual behavior
   - Environment details

### Feature Requests

1. Check if feature already exists or planned
2. Create issue with:
   - Clear title
   - Description
   - Use cases
   - Proposed API (if applicable)

### Code Contributions

#### New Features

1. Create feature branch
2. Implement feature
3. Write tests
4. Update documentation
5. Create PR

#### Bug Fixes

1. Create fix branch
2. Write test reproducing bug
3. Fix bug
4. Verify test passes
5. Create PR

#### Documentation

1. Edit relevant .md files
2. Keep language clear and concise
3. Include examples
4. Create PR

#### Tests

1. Add test in appropriate directory
2. Follow existing patterns
3. Cover edge cases
4. Keep tests independent

## Coding Standards

### PSR-12 Compliance

All code must follow PSR-12 standards. Key points:

- 4 spaces indentation
- camelCase for methods and properties
- PascalCase for classes and namespaces
- Type declarations everywhere
- `declare(strict_types=1)`
- `readonly` for value objects
- `#[Override]` for overrides

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

### Documentation

```php
/**
 * Short description.
 *
 * Longer description if needed.
 *
 * @param string $name User name
 * @param int $age User age
 * @return User Created user
 * @throws \InvalidArgumentException If name is empty
 */
public function createUser(string $name, int $age): User
{
    // ...
}
```

### Tests

```php
public function testCreateUser(): void
{
    $result = $this->service->createUser('John', 30);
    
    $this->assertInstanceOf(User::class, $result);
    $this->assertSame('John', $result->getName());
    $this->assertSame(30, $result->getAge());
}
```

## Review Process

1. Automated checks run on PR
2. Maintainers review code
3. Feedback provided
4. Changes requested (if needed)
5. PR approved and merged

### Review Criteria

- ✅ PSR-12 compliance
- ✅ Tests passing
- ✅ Code coverage maintained
- ✅ Documentation updated
- ✅ No breaking changes
- ✅ Code is readable
- ✅ Follows project patterns

## Release Process

1. Changes merged to `develop`
2. Testing and bug fixes
3. Version bump in `composer.json`
4. Tag created
5. Changes merged to `main`
6. Release published

## Community

- [Discord](https://discord.gg/gsmsdk) - Chat with the community
- [Issues](https://github.com/pkgmcp/GSMSDK/issues) - Report bugs
- [Discussions](https://github.com/pkgmcp/GSMSDK/discussions) - Ask questions

## Recognition

Contributors are recognized in:
- Release notes
- CONTRIBUTORS.md
- GitHub contributors list

Thank you for contributing! 🎉

---

*Questions? Open an issue or join our [Discord](https://discord.gg/gsmsdk).*
