# GSMSDK

> **GSMSDK** — Full-Stack PHP 8.5+ Framework for Desktop & Mobile Applications

[![PHP ≥ 8.5](https://img.shields.io/badge/PHP-%3E%3D8.5-8892bf)](https://php.net)  
[![License: MIT](https://img.shields.io/badge/License-MIT-blue)](LICENSE)  

## Overview

GSMSDK is a modern, full-stack PHP framework designed for building desktop and mobile applications with native capabilities. Built entirely with PHP 8.5+ features, it provides a clean, type-safe, and developer-friendly experience with integrated ADB and Fastboot protocol support.

## Features

### 🏗️ Core
- **Dependency Injection Container** - PSR-11 compatible
- **HTTP Layer** - Request/Response objects with validation
- **Database** - Fluent query builder for MySQL, PostgreSQL, SQLite
- **Configuration** - Dot-notation access, environment-aware
- **Error Handling** - Centralized exception handling

### 💻 Desktop
- **Window Management** - Create and configure application windows
- **Electron Integration** - Seamless Electron.js integration ready
- **Menu System** - Native menu bars
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

### ⚙️ CLI
- **Console Commands** - Custom CLI commands
- **Task Scheduling** - Cron-like job scheduling

### 🔧 Device Management
- **ADB Integration** - Full Android Debug Bridge protocol
- **Fastboot Integration** - Bootloader mode operations
- **Unified Interface** - Switch ADB ↔ Fastboot seamlessly

## Requirements

- PHP 8.5 or higher
- Composer 2.0+
- PDO extension (with MySQL/SQLite/PostgreSQL)
- JSON extension
- Mbstring extension

## Installation

### Quick Start

```bash
# Install as dependency
composer require pkgmcp/gsmsdk
```

### Manual Installation

```bash
git clone https://github.com/pkgmcp/gsmsdk.git
cd gsmsdk
composer install
```

## Quick Example

### Desktop Application

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

### Mobile App Configuration

```php
use GSMSDK\Mobile\App;

$app = new App([
    'name' => 'My Mobile App',
    'identifier' => 'com.example.app',
    'version' => '1.0.0',
]);

$app->addPlatform('android')
    ->addPlatform('ios')
    ->addPermission('android.permission.INTERNET');

echo $app->generateAndroidManifest();
```

### Database Query

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

### Device Management

```php
use GSMSDK\DeviceManager;

$manager = new DeviceManager();

// Connect via ADB
$adb = $manager->connectADB('emulator-5554');
$props = $adb->getProperties();

// Switch to Fastboot
$manager->switchToFastboot();

// Connect via Fastboot
$fastboot = $manager->getFastbootDevice();
$fastboot->flash('boot', '/path/to/boot.img');
```

## Project Structure

```
gsmsdk/
├── src/
│   ├── Core/              # Application core (DI, Controller)
│   ├── HTTP/              # HTTP layer (Request, Response)
│   ├── Database/          # Database (Connection, QueryBuilder)
│   ├── CLI/               # Console interface
│   ├── Desktop/           # Desktop app support
│   ├── Mobile/            # Mobile app config
│   ├── Fastboot/          # Fastboot integration
│   ├── ADB/               # ADB integration
│   ├── Contracts/         # Interfaces
│   ├── Traits/            # Reusable traits
│   └── Exceptions/        # Exception classes
├── examples/              # Example applications
├── public/                # Web entry point
└── tests/                 # Test suite
```

## PHP 8.5+ Features

- **Readonly Classes** - Immutable value objects
- **Typed Properties** - Type safety everywhere
- **Named Arguments** - Self-documenting code
- **Union Types** - Flexible type hints
- **Attributes** - Metadata annotations
- **Enums** - Type-safe constants
- **Match Expressions** - Clean conditional logic
- **Constructor Promotion** - Concise constructors

## Integration Libraries

GSMSDK integrates the following official packages:

- **[adb-php](https://github.com/pkgmcp/adb-php)** - ADB protocol implementation
- **[fastboot-php](https://github.com/pkgmcp/fastboot-php)** - Fastboot protocol implementation

## Testing

```bash
# Install dependencies
composer install

# Run tests
composer test
```

## Documentation

- [API Reference](docs/API.md)
- [Desktop Guide](docs/DESKTOP.md)
- [Mobile Guide](docs/MOBILE.md)
- [Database Guide](docs/DATABASE.md)

## License

MIT License - see [LICENSE](LICENSE) file.

---

**Built with ❤️ for the PHP community**
