# GSMSDK

> **GSMSDK** — Full-Stack PHP 8.5+ Framework for Desktop & Mobile Applications with ADB & Fastboot Integration

[![PHP ≥ 8.5](https://img.shields.io/badge/PHP-%3E%3D8.5-8892bf)](https://php.net)  
[![License: MIT](https://img.shields.io/badge/License-MIT-blue)](LICENSE)  
[![Tests](https://img.shields.io/badge/Tests-passing-brightgreen)](tests/)  

---

## Overview

GSMSDK is a modern, enterprise-grade PHP framework designed for building **desktop**, **mobile**, and **web applications** with native capabilities and integrated Android device management. Built entirely with PHP 8.5+ features, it provides a clean, type-safe, and developer-friendly experience.

## 🚀 Quick Start

### Installation

```bash
# Install via Composer
composer require pkgmcp/gsmsdk

# Clone repository
git clone https://github.com/pkgmcp/GSMSDK.git
cd GSMSDK
composer install
```

### Basic Application

```php
<?php
use GSMSDK\Core\MvcApplication;

require 'vendor/autoload.php';

$app = new MvcApplication([
    'debug' => true,
    'environment' => 'development',
    'paths' => [
        'views' => __DIR__ . '/resources/views',
        'controllers' => __DIR__ . '/app/Controllers',
    ],
]);

// Define routes
$app->get('/', 'HomeController@index');
$app->get('/dashboard', 'DashboardController@index');

// Run application
$app->run();
```

## 🎨 Features

### 🏗️ Core Architecture
- **Dependency Injection Container** - PSR-11 compatible with lazy loading
- **HTTP Layer** - Full PSR-7 inspired Request/Response objects
- **MVC Framework** - Clean separation with View renderer
- **Fluent Query Builder** - Type-safe database operations
- **Event System** - Hook into application lifecycle
- **Macro System** - Extend classes at runtime
- **Configuration Management** - Dot-notation access

### 💻 Desktop Applications
- **Window Management** - Create and configure windows
- **Electron Integration** - Seamless Electron.js support
- **Menu System** - Native menu bars
- **Tray Icons** - System tray integration
- **Auto-updates** - Built-in update mechanism

### 📱 Mobile Applications
- **Android/iOS Support** - Cross-platform development
- **Manifest Generation** - Auto-generate AndroidManifest.xml & Info.plist
- **Permission Management** - Declarative permission system
- **Native APIs** - Camera, GPS, Notifications, Storage
- **App Configuration** - Bundle ID, version, build management

### 🔌 Android Integration
- **ADB Protocol** - Full Android Debug Bridge implementation
- **Fastboot Protocol** - Bootloader mode operations
- **Device Management** - List, connect, query devices
- **Shell Commands** - Execute ADB shell commands
- **APK Operations** - Install, uninstall, clear apps
- **File Transfers** - Push/pull files to/from device
- **Screen Capture** - Take screenshots
- **LogCat** - Read device logs
- **Port Forwarding** - TCP forward and reverse proxies

## 📖 Documentation

- [Quick Start Guide](docs/QUICKSTART.md)
- [API Reference](docs/API.md)
- [Desktop Guide](docs/DESKTOP.md)
- [Mobile Guide](docs/MOBILE.md)
- [Database Guide](docs/DATABASE.md)
- [ADB & Fastboot Guide](docs/ANDROID.md)
- [Security Policy](SECURITY.md)
- [Changelog](CHANGELOG.md)

## 🛠️ Architecture

```
gsmsdk/
├── src/
│   ├── Core/              # Application core
│   │   ├── Application.php      # Base DI container
│   │   ├── MvcApplication.php   # MVC framework
│   │   ├── Controller.php       # Base controller
│   │   ├── Model.php            # ActiveRecord base
│   │   └── View.php             # View renderer
│   ├── HTTP/               # HTTP layer
│   │   ├── Request.php          # PSR-7 Request
│   │   └── Response.php         # PSR-7 Response
│   ├── Database/           # Database layer
│   │   ├── Connection.php       # PDO wrapper
│   │   └── QueryBuilder.php     # Fluent ORM
│   ├── CLI/                # Console interface
│   │   └── Console.php          # Command runner
│   ├── Desktop/            # Desktop support
│   │   ├── Application.php      # Desktop app
│   │   └── Window.php           # Window manager
│   ├── Mobile/             # Mobile support
│   │   └── App.php              # App config
│   ├── Fastboot/           # Fastboot wrapper
│   ├── ADB/                # ADB wrapper
│   ├── Contracts/          # Interfaces
│   ├── Traits/             # Reusable traits
│   └── Exceptions/         # Exception classes
├── app/
│   ├── Controllers/        # Application controllers
│   ├── Models/             # Application models
│   └── Views/              # Application views
├── resources/
│   ├── views/              # View templates
│   │   ├── layouts/        # Layout templates
│   │   └── partials/       # Partial templates
│   └── assets/             # Static assets
├── public/                 # Web root
├── tests/                  # Test suite
└── config/                 # Configuration files
```

## 🎯 Use Cases

### Web Applications
```php
// Create RESTful APIs
$app->get('/api/users', 'UserController@index');
$app->post('/api/users', 'UserController@store');
```

### Desktop Applications
```php
// Build cross-platform desktop apps
$app = new Desktop\Application();
$window = $app->createWindow([
    'title' => 'My App',
    'width' => 1200,
    'height' => 800,
]);
```

### Mobile Applications
```php
// Configure mobile apps
$app = new Mobile\App([
    'name' => 'My App',
    'identifier' => 'com.example.app',
]);
$app->addPermission('android.permission.INTERNET');
echo $app->generateAndroidManifest();
```

### Android Device Management
```php
// ADB operations
use GSMSDK\ADB\ADBDevice;

$adb = new ADBDevice();
$adb->connect('device-serial');
$adb->install('/path/to/app.apk');
$adb->shell('pm list packages');

// Fastboot operations
use GSMSDK\Fastboot\FastbootDevice;

$fastboot = new FastbootDevice();
$fastboot->connect('device-serial');
$fastboot->flash('boot', '/path/to/boot.img');
$fastboot->reboot();
```

## ⚡ PHP 8.5+ Features

GSMSDK leverages modern PHP features:

- **Readonly Classes** - Immutable value objects
- **Typed Properties** - Type safety throughout
- **Named Arguments** - Self-documenting code
- **Union Types** - Flexible type hints
- **Attributes** - Metadata annotations
- **Enums** - Type-safe constants
- **Match Expressions** - Clean conditional logic
- **Constructor Promotion** - Concise constructors
- **Fiber Support** - Async operations (when needed)
- **First-class Callables** - Clean callback syntax

## 🧪 Testing

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run with coverage
composer coverage
```

## 📦 Dependencies

- **Required**: PHP 8.5+, PDO, JSON, Mbstring
- **ADB Integration**: pkgmcp/adb-php ^1.0
- **Fastboot Integration**: pkgmcp/fastboot-php ^1.0
- **Dev**: PHPUnit ^11.0

## 🔄 Integration Libraries

GSMSDK integrates with our protocol libraries:

- **[adb-php](https://github.com/pkgmcp/adb-php)** - Full ADB protocol implementation
- **[fastboot-php](https://github.com/pkgmcp/fastboot-php)** - Fastboot protocol implementation

## 🌍 Ecosystem

- **[Android PHP SDK Demo](https://github.com/pkgmcp/android-php-sdk-demo)** - Web UI for ADB/Fastboot
- **[ADB PHP](https://github.com/pkgmcp/adb-php)** - ADB protocol library
- **[Fastboot PHP](https://github.com/pkgmcp/fastboot-php)** - Fastboot protocol library

## 🤝 Contributing

We welcome contributions! Please read our [Contributing Guide](CONTRIBUTING.md) first.

### Development Setup

```bash
git clone https://github.com/pkgmcp/gsmsdk.git
cd gsmsdk
composer install
composer test
```

## 📄 License

MIT License - see [LICENSE](LICENSE) file.

---

**Built with ❤️ for the PHP community**

[![Discord](https://img.shields.io/badge/Discord-Join-blue?logo=discord)](https://discord.gg/gsmsdk)  
[![Documentation](https://img.shields.io/badge/Docs-Read-blue)](https://gsmsdk.io/docs)  
[![Issues](https://img.shields.io/badge/Issues-Report-red)](https://github.com/pkgmcp/gsmsdk/issues)

## 🎨 GSM Templating Engine

GSMSDK includes a powerful templating engine called **GSM** (GSMSDK Template), inspired by Laravel's Blade. It provides a clean, concise syntax while compiling to optimized PHP code.

### Syntax Overview

#### Echo Statements

```php
{{ $variable }}      {# Escaped output #}
{!! $html !!}        {# Raw output #}
@php($code)          {# Execute PHP #}
```

#### Control Structures

```php
@if ($condition)
  // if block
@elseif ($other)
  // elseif block
@else
  // else block
@endif

@unless ($condition)
  // unless block
@endunless

@foreach ($items as $item)
  {{ $item }}
@endforeach

@for ($i = 0; $i < 10; $i++)
  {{ $i }}
@endfor

@while ($condition)
  // while block
@endwhile
```

#### Template Inheritance

```php
{{-- resources/views/layouts/main.gsm.php --}}
<!DOCTYPE html>
<html>
<head>
  <title>{{ $title }}</title>
</head>
<body>
  @yield('content')
</body>
</html>

{{-- resources/views/home.gsm.php --}}
@extends('layouts/main')

@section('content')
  <h1>Welcome!</h1>
@endsection
```

#### Components & Slots

```php
{{-- Using components --}}
@component('components.card')
  This is the slot content!
@endcomponent
```

#### Includes

```php
@include('partials.header')
@include('partials.nav')
@include('partials.footer')
```

#### Forms & Security

```php
<form method="POST">
  @csrf
  <input type="text" name="username">
  <button type="submit">Submit</button>
</form>

<form method="POST">
  @method('PUT')
  <button>Update</button>
</form>
```

#### Auth & Session

```php
@auth
  <p>User is authenticated</p>
@endauth

@guest
  <p>User is a guest</p>
@endguest
```

#### Error Handling

```php
@error('email')
  <span class="error">{{ $errors['email'] }}</span>
@enderror
```

#### Helpers

```php
@selected($a == $b)     {# selected="selected" #}
@checked($active)       {# checked="checked" #}
@isset($variable)       {# Check if set #}
@empty($variable)       {# Check if empty #}
@can('edit-post')       {# Check ability #}
@endcan
```

#### Dump & Debug

```php
@dump($variable)        {# Dump variable #}
@dd($variable)          {# Dump and die #}
```

### Performance

GSM templates are **compiled to native PHP code** and cached for maximum performance. The compilation happens only when templates are modified.

### Features

✅ Blade-inspired syntax  
✅ Template inheritance with `@extends`  
✅ Components and slots  
✅ Control structures (if, foreach, for, while)  
✅ Partial includes  
✅ CSRF protection  
✅ Auth directives  
✅ Error handling  
✅ Helper functions  
✅ Compiled caching  
✅ Type-safe  
✅ IDE-friendly  

### Examples

See the included templates:
- `resources/views/layouts/main.gsm.php` - Main layout
- `resources/views/home.gsm.php` - Home page
- `resources/views/example.gsm.php` - Syntax examples

### Usage

```php
use GSMSDK\Core\View;

$view = new View('resources/views', 'resources/views/layouts');

echo $view->render('home', [
    'title' => 'My Page',
    'items' => ['Apple', 'Banana', 'Cherry'],
]);
```

---

**GSM** makes templating clean, fast, and enjoyable! 🚀

## 🧪 Testing

### Test Suite

GSMSDK uses PHPUnit-style tests with Pest-inspired syntax.

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test class
vendor/bin/phpunit tests/Unit/Core/ApplicationTest

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Test Categories

| Category | Tests | Lines |
|----------|-------|-------|
| Core/Application | 8 | 260 |
| Core/View | 12 | 460 |
| Core/Auth | 15 | 507 |
| HTTP/Request | 20 | 523 |
| HTTP/Response | 13 | 528 |
| Database/Connection | 9 | 606 |
| Database/QueryBuilder | 24 | 1,048 |
| Engine/GsmTest | - | - |
| MvcApplication | - | - |
| **Total** | **7+ tests** | **1,571+ lines** |

### Sample Test

```php
public function it_filters_with_where_clause(): void {
    $results = $this->builder->table('users')
        ->where('age', '>', 25)
        ->get();
    
    $this->assertCount(2, $results);
}
```

### Pest Configuration

```php
// tests/Pest.php
beforeEach(function () {
    $this->app = new Application([
        'debug' => true,
        'environment' => 'testing'
    ]);
});
```

