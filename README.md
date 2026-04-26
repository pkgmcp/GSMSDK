# GSMSDK

> **GSMSDK** — Full-Stack PHP 8.5+ Framework for Desktop & Mobile Applications

[![PHP ≥ 8.5](https://img.shields.io/badge/PHP-%3E%3D8.5-8892bf)](https://php.net)  
[![License: MIT](https://img.shields.io/badge/License-MIT-blue)](LICENSE)  
[![Discord](https://img.shields.io/badge/Discord-Join-blue?logo=discord)](https://discord.gg/gsmsdk)

---

## Overview

GSMSDK is a modern, full-stack PHP framework designed for building desktop and mobile applications with native capabilities. Built entirely with PHP 8.5+ features, it provides a clean, type-safe, and developer-friendly experience.

## Features

### 🏗️ Core
- **Dependency Injection Container** - PSR-11 compatible
- **HTTP Layer** - Request/Response objects with validation
- **Database** - Fluent query builder for MySQL, PostgreSQL, SQLite
- **Configuration** - Dot-notation access, environment-aware
- **Error Handling** - Centralized exception handling
- **Logging** - Structured logging with multiple channels

### 💻 Desktop
- **Window Management** - Create and configure application windows
- **Electron Integration** - Seamless Electron.js integration
- **Menu System** - Native menu bars
- **Tray Icons** - System tray support
- **Auto-updates** - Built-in update mechanism

### 📱 Mobile
- **Android/iOS** - Cross-platform mobile development
- **Permissions** - Declarative permission system
- **Native APIs** - Camera, GPS, Notifications, Storage
- **App Config** - Bundle identifier, version management
- **Manifest Generation** - Auto-generate AndroidManifest.xml & Info.plist

### 🌐 Web & API
- **REST API** - Build RESTful services
- **CORS Support** - Built-in CORS handling
- **Middleware** - Request/response middleware pipeline
- **Rate Limiting** - API rate limiting
- **Authentication** - JWT & OAuth ready

### ⚙️ CLI
- **Console Commands** - Custom CLI commands
- **Task Scheduling** - Cron-like job scheduling
- **Interactive Prompts** - User input handling
- **Output Formatting** - Tables, progress bars, spinners

## Requirements

- PHP 8.5 or higher
- Composer 2.0+
- PDO extension (with MySQL/SQLite/PostgreSQL)
- JSON extension
- Mbstring extension

## Installation

### Quick Start

```bash
# Create a new project
composer create-project pkgmcp/gsmsdk my-app

# Or install as dependency
composer require pkgmcp/gsmsdk
```

### Manual Installation

```bash
git clone https://github.com/pkgmcp/gsmsdk.git
cd gsmsdk
composer install
```

## Quick Examples

### 1. Basic Application

```php
use GSMSDK\Core\Application;

$app = new Application([
    'debug' => true,
    'environment' => 'development',
]);

echo $app; // GSMSDK v1.0.0 [development]
```

### 2. Desktop Application

```php
use GSMSDK\Desktop\Application;
use GSMSDK\Desktop\Window;

$app = new Application();

$window = $app->createWindow([
    'title' => 'My Desktop App',
    'width' => 1200,
    'height' => 800,
    'resizable' => true,
]);

$app->run();
```

### 3. Mobile App Configuration

```php
use GSMSDK\Mobile\App;

$app = new App([
    'name' => 'My Mobile App',
    'identifier' => 'com.example.app',
    'version' => '1.0.0',
]);

$app->addPlatform('android')
    ->addPlatform('ios')
    ->addPermission('android.permission.INTERNET')
    ->addCapability('push_notifications');

echo $app->generateAndroidManifest();
echo $app->generateInfoPlist();
```

### 4. Database Queries

```php
use GSMSDK\Database\Connection;
use GSMSDK\Database\QueryBuilder;

$db = new Connection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'myapp',
    'username' => 'root',
]);

$users = $db->query('SELECT * FROM users WHERE active = ?', [1])->fetchAll();

// Or use Query Builder
$qb = new QueryBuilder($db);
$users = $qb->table('users')
    ->where('active', 1)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();
```

### 5. HTTP Request/Response

```php
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

$request = new Request();
$response = new Response();

$name = $request->input('name', 'Guest');

$response->status(200)
    ->header('Content-Type', 'application/json')
    ->json(['message' => "Hello, {$name}!"])
    ->send();
```

### 6. CLI Console

```php
use GSMSDK\CLI\Console;
use GSMSDK\Core\Application;

$app = new Application();
$console = new Console($app);

$console->command('greet', 'Greet a user', function ($args) {
    echo "Hello, {$args[0]}!\n";
});

$console->run($argv);
```

## Project Structure

```
gsmsdk/
├── src/
│   ├── Core/              # Application core
│   │   ├── Application.php
│   │   └── Controller.php
│   ├── HTTP/              # HTTP layer
│   │   ├── Request.php
│   │   └── Response.php
│   ├── Database/          # Database components
│   │   ├── Connection.php
│   │   └── QueryBuilder.php
│   ├── CLI/               # Console interface
│   │   └── Console.php
│   ├── Desktop/           # Desktop app support
│   │   ├── Application.php
│   │   └── Window.php
│   ├── Mobile/            # Mobile app support
│   │   └── App.php
│   ├── Contracts/         # Interfaces
│   ├── Traits/            # Reusable traits
│   └── Exceptions/        # Exception classes
├── public/                # Web entry point
│   └── index.php
├── examples/              # Example applications
├── config/                # Configuration files
├── storage/               # Runtime files
└── tests/                 # Test suite
```

## Architecture

### Request Lifecycle

1. **Bootstrap** - Application initializes with configuration
2. **Request** - HTTP request is captured and parsed
3. **Routing** - Request is matched to handler
4. **Middleware** - Request passes through middleware stack
5. **Handler** - Controller/action processes request
6. **Response** - Response is built and sent

### Dependency Injection

The container uses lazy loading and supports:
- Constructor injection
- Method injection
- Property injection (via macros)
- Service binding and resolution

### Database Layer

- Fluent query builder
- Parameterized queries (SQL injection safe)
- Transaction support
- Multiple database drivers

## PHP 8.5+ Features

GSMSDK leverages modern PHP features:

- **Readonly Classes** - Immutable value objects
- **Typed Properties** - Type safety everywhere
- **Named Arguments** - Self-documenting code
- **Union Types** - Flexible type hints
- **Attributes** - Metadata annotations
- **Enums** - Type-safe constants
- **Match Expressions** - Clean conditional logic
- **Constructor Property Promotion** - Concise constructors

## Testing

```bash
# Run tests
composer test

# Run with coverage
composer coverage
```

## Documentation

- [API Reference](docs/API.md)
- [Desktop Guide](docs/DESKTOP.md)
- [Mobile Guide](docs/MOBILE.md)
- [Database Guide](docs/DATABASE.md)
- [CLI Guide](docs/CLI.md)

## Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) first.

### Development Setup

```bash
git clone https://github.com/pkgmcp/gsmsdk.git
cd gsmsdk
composer install
composer test
```

## License

MIT License - see [LICENSE](LICENSE) file.

## Support

- 📖 [Documentation](https://gsmsdk.io/docs)
- 💬 [Discord Community](https://discord.gg/gsmsdk)
- 🐛 [Issue Tracker](https://github.com/pkgmcp/gsmsdk/issues)
- 📧 Email: dev@gsmsdk.io

## Acknowledgments

- PHP Core Team for the amazing language
- Symfony Components for inspiration
- Laravel for developer experience patterns

---

**Built with ❤️ for the PHP community**
