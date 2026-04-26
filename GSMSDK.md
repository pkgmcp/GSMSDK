# GSMSDK - Complete Documentation

## 🚀 Overview

GSMSDK (GSMSDK Full-Stack PHP Framework) is a comprehensive, modern PHP framework for building full-stack applications with Android device management capabilities.

**Version**: 2.0.0  
**License**: MIT  
**PHP**: 8.5+  
**GitHub**: https://github.com/pkgmcp/GSMSDK

---

## 📚 Table of Contents

1. [Quick Start](#-quick-start)
2. [Architecture](#-architecture)
3. [Routing System](#-routing-system)
4. [Templating Engine](#-templating-engine)
5. [Authentication](#-authentication)
6. [Database](#-database)
7. [API System](#-api-system)
8. [Android Integration](#-android-integration)
9. [UI Components](#-ui-components)
10. [Admin Dashboard](#-admin-dashboard)
11. [Security](#-security)
12. [Deployment](#-deployment)
13. [Testing](#-testing)

---

## ⚡ Quick Start

### Installation

```bash
# Using Composer
composer require pkgmcp/gsmsdk

# Or clone repository
git clone https://github.com/pkgmcp/GSMSDK.git
cd GSMSDK
composer install
```

### Basic Setup

```php
<?php
require_once 'vendor/autoload.php';

use GSMSDK\Core\Application;

$app = new Application([
    'environment' => 'development',
    'debug' => true,
    'paths' => [
        'base' => dirname(__DIR__),
        'views' => dirname(__DIR__) . '/resources/views',
    ],
]);

// Route definition
$app->get('/', function ($request, $response) {
    return $response->body('<h1>Hello GSMSDK!</h1>');
});

$app->run();
```

### Using Router

```php
use GSMSDK\HTTP\Router;

$router = new Router();

// Named routes
$router->get('/', 'HomeController@index')->name('home');

// Route parameters
$router->get('/users/{id:\d+}', 'UserController@show');

// Resource controllers
$router->resource('/posts', 'PostController');

// Route groups
$router->group(['prefix' => '/admin', 'middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', 'AdminController@index');
});

$router->dispatch($request);
```

---

## 🏗️ Architecture

### Core Components

```
GSMSDK/
├── Core/
│   ├── Application.php      # DI Container
│   ├── MvcApplication.php   # MVC Framework
│   ├── Auth/                # Authentication
│   │   └── AuthManager.php
│   ├── AI/                  # Smart Routing
│   │   └── AiRouter.php
│   └── Api/                 # API Application
│       └── ApiApplication.php
├── HTTP/
│   ├── Request.php          # Request handling
│   ├── Response.php         # Response building
│   └── Router.php           # Route management
├── Database/
│   ├── Connection.php       # Database connection
│   └── QueryBuilder.php     # Fluent query builder
├── Middleware/
│   └── ApiMiddleware.php    # API middleware
└── Core/Engine/             # Template engine
    └── GSM.php
```

### Design Principles

1. **Type Safety**: PHP 8.5+ strict types throughout
2. **Dependency Injection**: PSR-11 container
3. **SOLID**: Single responsibility, open-closed, etc.
4. **Security First**: XSS, SQL injection, CSRF protection
5. **Performance**: Lazy loading, caching, optimized queries

---

## 🛣️ Routing System

### Basic Routes

```php
$router->get('/', 'HomeController@index');
$router->post('/users', 'UserController@store');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@destroy');
```

### Route Parameters

```php
// Required
$router->get('/users/{id}', 'UserController@show');

// Optional
$router->get('/pages/{slug?}', 'PageController@show');

// Regex constraints
$router->get('/posts/{id:\d+}', 'PostController@show');
$router->get('/pages/{slug:[a-z-]+}', 'PageController@show');
```

### Named Routes

```php
$router->get('/users/{id}', 'UserController@show')->name('users.show');

// Generate URL
$url = $router->route('users.show', ['id' => 123]);
```

### Route Groups

```php
$router->group(['prefix' => '/admin', 'namespace' => 'Admin'], function ($router) {
    $router->get('/dashboard', 'DashboardController@index');
    $router->resource('/users', 'UserController');
});
```

### Resource Controllers

```php
// Full resource
$router->resource('/posts', 'PostController');

// API resource (no create/edit)
$router->apiResource('/posts', 'PostController');

// Only specific methods
$router->resource('/posts', 'PostController', ['only' => ['index', 'show']]);
```

### Middleware

```php
$router->get('/admin', 'AdminController@index')->middleware('auth');

$router->group(['middleware' => ['auth', 'csrf']], function ($router) {
    // All routes use auth and CSRF
});
```

### Model Binding

```php
$router->model('user', 'App\Models\User');

$router->model('device', 'App\Models\Device', function ($id) {
    return Device::where('serial', $id)->firstOrFail();
});
```

### Route Caching

```php
// Cache routes
$router->cache('storage/routes.cache');

// Load cached routes
$router->loadCache('storage/routes.cache');
```

---

## 🎨 Templating Engine

### Basic Syntax

```php
{{ $variable }}              {# Escaped output #}
{!! $html !!}                {# Raw output #}
@php($code)                  {# Execute PHP #}
```

### Control Structures

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

### Template Inheritance

```php
{{-- Layout --}}
<!DOCTYPE html>
<html>
<head>
  <title>{{ $title }}</title>
</head>
<body>
  @yield('content')
</body>
</html>

{{-- Child --}}
@extends('layouts/main')

@section('content')
  <h1>Page Content</h1>
@endsection
```

### Components and Slots

```php
{{-- Using component --}}
@component('components.card')
  This is the slot content!
@endcomponent
```

### Includes

```php
@include('partials.header')
@include('partials.nav')
@include('partials.footer')
```

### Forms and Security

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

### Auth Directives

```php
@auth
  <p>User is authenticated</p>
@endauth

@guest
  <p>User is a guest</p>
@endguest
```

### Error Handling

```php
@error('email')
  <span class="error">{{ $errors['email'] }}</span>
@enderror
```

### Helpers

```php
@selected($a == $b)     {# selected="selected" #}
@checked($active)       {# checked="checked" #}
@isset($variable)       {# Check if set #}
@empty($variable)       {# Check if empty #}
@can('edit-post')       {# Check ability #}
@endcan
```

### Dump and Debug

```php
@dump($variable)        {# Dump variable #}
@dd($variable)          {# Dump and die #}
```

---

## 🔐 Authentication

### AuthManager

```php
use GSMSDK\Core\Auth\AuthManager;

$auth = $app->get('auth');

// Login
if ($auth->attempt(['email' => 'user@example.com', 'password' => 'secret'])) {
    // User authenticated
    $user = $auth->user();
}

// Check auth
if ($auth->check()) {
    // User is logged in
}

// Logout
$auth->logout();
```

### Roles and Permissions

```php
// Check role
if ($auth->hasRole('admin')) {
    // Is admin
}

// Check permission
if ($auth->can('edit-posts')) {
    // Can edit posts
}
```

### CSRF Protection

```php
// Get token
$token = $auth->csrfToken();

// Validate token
if ($auth->validateCsrf($token)) {
    // Token is valid
}
```

### Rate Limiting

```php
// Apply rate limit
$result = $auth->rateLimitMiddleware($request, 'api', 60, 60);
if ($result) {
    return $result; // 429 Too Many Requests
}
```

### API Token Authentication

```php
// Authenticate with token
if ($auth->authenticateWithToken('api-token-here')) {
    // Token is valid
}

// Generate new token
$token = $auth->generateApiToken();
```

### Middleware

```php
// Auth middleware
$auth->authMiddleware($request);

// Role middleware
$roleMiddleware = $auth->roleMiddleware('admin');

// Permission middleware
$permissionMiddleware = $auth->permissionMiddleware('edit-posts');
```

---

## 🗄️ Database

### Connection

```php
use GSMSDK\Database\Connection;

$db = new Connection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'myapp',
    'username' => 'root',
    'password' => 'secret',
    'charset' => 'utf8mb4',
]);
```

### Query Builder

```php
use GSMSDK\Database\QueryBuilder;

$qb = new QueryBuilder($db);

// Select
$users = $qb->table('users')
    ->select(['id', 'name', 'email'])
    ->where('active', '=', 1)
    ->get();

// Where conditions
$qb->where('age', '>', 18);
$qb->where('name', 'LIKE', '%John%');

// Order and limit
$qb->orderBy('created_at', 'DESC')
   ->limit(10);

// Joins
$qb->join('posts', 'users.id', '=', 'posts.user_id');

// Aggregates
$count = $qb->count();
```

### Insert

```php
$id = $qb->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
```

### Update

```php
$affected = $qb->table('users')
    ->where('id', '=', 123)
    ->update(['active' => 1]);
```

### Delete

```php
$affected = $qb->table('users')
    ->where('id', '=', 123)
    ->delete();
```

### Raw Queries

```php
$results = $db->query('SELECT * FROM users WHERE id = ?', [123]);
$user = $db->fetch('SELECT * FROM users WHERE id = ?', [123]);
```

---

## 🌐 API System

### API Router

```php
use GSMSDK\Core\AI\AiRouter;

$aiRouter = new AiRouter($app);

// Register API route
$aiRouter->register('GET', '/api/users', function ($request) {
    return ['users' => []];
}, [
    'description' => 'List all users',
    'tags' => ['users'],
    'auth' => true,
    'throttle' => true,
    'cache' => true,
    'cache_ttl' => 300,
    'validation' => [
        'page' => 'integer|min:1',
        'limit' => 'integer|min:1|max:100',
    ],
    'response_codes' => [
        200 => 'Success',
        401 => 'Unauthorized',
    ],
]);
```

### API Application

```php
use GSMSDK\Core\Api\ApiApplication;

$api = new ApiApplication($app);
$api->run();
```

### API Routes

```php
// routes/api.php
return function ($router) {
    $router->group(['prefix' => '/api', 'middleware' => ['auth']], function ($router) {
        $router->get('/users', 'UserController@index');
        $router->post('/users', 'UserController@store');
    });
};
```

### API Middleware

```php
use GSMSDK\Middleware\ApiMiddleware;

// Rate limiting
$result = ApiMiddleware::throttle($request);

// Validate content type
$result = ApiMiddleware::validateContentType($request);

// CORS headers
$corsResponse = ApiMiddleware::addCorsHeaders($request);
```

---

## 🤖 Android Integration

### ADB Device

```php
use GSMSDK\ADB\ADBDevice;

$adb = new ADBDevice('192.168.1.100:5555');

// Connect
$adb->connect();

// Shell command
$output = $adb->shell('ls /sdcard');

// Install APK
$adb->installApp('/path/to/app.apk');

// Uninstall app
$adb->uninstallApp('com.example.app');

// Push file
$adb->pushFile('/local/file.txt', '/sdcard/file.txt');

// Pull file
$adb->pullFile('/sdcard/file.txt', '/local/file.txt');

// Screenshot
$image = $adb->screenCapture();

// Logcat
$logs = $adb->readLogcat();

// Reboot
$adb->reboot();
```

### Fastboot Device

```php
use GSMSDK\Fastboot\FastbootDevice;

$fastboot = new FastbootDevice();

// Connect
$fastboot->connect();

// Flash partition
$fastboot->flash('boot', '/path/to/boot.img');

// Erase partition
$fastboot->erase('cache');

// Reboot
$fastboot->reboot();

// Get variable
$var = $fastboot->getvar('version');
```

### Device Manager

```php
use GSMSDK\DeviceManager;

$manager = new DeviceManager();

// List devices
$devices = $manager->getDevices();

// Switch to fastboot
$manager->switchToFastboot($device);

// Reboot to bootloader
$manager->rebootToBootloader($device);
```

---

## 💻 Desktop & Mobile

### Window Manager

```php
use GSMSDK\Desktop\Window;

$window = new Window('My App', 800, 600);
$window->setResizable(true);
$window->setMenu($menu);
$window->show();
```

### Mobile App Config

```php
use GSMSDK\Mobile\App;

$app = new App('com.example.app');
$app->setDisplayName('My App');
$app->setVersion('1.0.0');
$app->addPermission('INTERNET');
$config = $app->generateConfig();
```

---

## 🎨 UI Components

### Button

```xml
@include('components.ui.button', [
    'type' => 'primary',
    'size' => 'md',
    'icon' => 'plus',
    'text' => 'Add Item'
])
```

### Card

```xml
@include('components.ui.card', [
    'elevated' => true,
    'hoverable' => true,
    'header' => 'Card Title',
    'body' => 'Card content'
])
```

### Alert

```xml
@include('components.ui.alert', [
    'type' => 'success',
    'title' => 'Success!',
    'message' => 'Operation completed'
])
```

### Badge

```xml
@include('components.ui.badge', [
    'type' => 'success',
    'text' => 'Active'
])
```

---

## 🛡️ Security

### XSS Protection

```php
// All {{ }} output is automatically escaped
{{ $userInput }} {# Safe #}

// Raw output requires explicit intent
{!! $trustedHtml !!} {# Only if trusted #}
```

### SQL Injection Prevention

```php
// Always use parameterized queries
$db->query('SELECT * FROM users WHERE id = ?', [$id]);

// Query builder uses bindings automatically
$qb->table('users')->where('id', '=', $id)->get();
```

### CSRF Protection

```php
// All forms should include CSRF token
@csrf

// Validated automatically
```

### Path Traversal Prevention

```php
// Template paths are validated
$realPath = realpath($template);
if (strpos($realPath, $cachePath) !== 0) {
    throw new \RuntimeException('Invalid template path');
}
```

### Rate Limiting

```php
// API routes are throttled by default
60 requests per minute per IP
```

### Session Security

```php
// Secure cookie settings
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
```

---

## 🚀 Deployment

### Production Configuration

```php
$app = new Application([
    'environment' => 'production',
    'debug' => false,
    'database' => [
        'driver' => 'mysql',
        'host' => 'db.example.com',
        'database' => 'production',
        'username' => 'prod_user',
        'password' => 'secure_password',
    ],
]);
```

### Optimization

```bash
# Enable OPcache
php -d opcache.enable=1

# Route caching
$router->cache('storage/routes.cache');

# Autoloader optimization
composer install --optimize-autoloader --no-dev
```

### File Permissions

```bash
chmod 750 storage/
chmod 770 storage/cache/
chown www-data:www-data storage/
```

### HTTPS

```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
}
```

---

## 🧪 Testing

### PHPUnit

```bash
# Run all tests
composer test

# Run specific test
./vendor/bin/phpunit tests/Unit/Engine/GsmTest.php

# Run with coverage
composer coverage
```

### Test Structure

```
tests/
├── Unit/
│   ├── Engine/
│   │   └── GsmTest.php
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

---

## 📊 Performance

### Benchmarks

- Template rendering: ~0.5ms
- Database query: ~2ms
- API response: ~5ms
- Full page load: ~50ms

### Optimization Tips

1. Enable OPcache
2. Use route caching
3. Minimize queries
4. Cache results
5. Serve static files via CDN
6. Compress responses
7. Use connection pooling

---

## 🔧 Configuration

### Application Config

```php
$config = [
    'debug' => false,
    'environment' => 'production',
    'paths' => [
        'base' => dirname(__DIR__),
        'views' => dirname(__DIR__) . '/resources/views',
    ],
    'app' => [
        'name' => 'GSMSDK',
        'url' => 'https://example.com',
        'version' => '2.0.0',
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'app',
        'username' => 'root',
        'password' => '',
    ],
    'api' => [
        'rate_limit' => 100,
        'rate_window' => 60,
        'throttle' => true,
        'cache' => true,
    ],
];
```

---

## 🆘 Support

### Documentation

- [Quick Start](#-quick-start)
- [API Reference](https://github.com/pkgmcp/GSMSDK/wiki/API)
- [UI Components](UI.md)

### Community

- GitHub Issues: https://github.com/pkgmcp/GSMSDK/issues
- Discord: https://discord.gg/clawd
- Wiki: https://github.com/pkgmcp/GSMSDK/wiki

### Professional Support

For enterprise support, contact: support@gsmsdk.io

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Update documentation
6. Submit a pull request

See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📄 License

MIT License - see [LICENSE](LICENSE) file.

---

## ⭐ Acknowledgments

- PHP Community
- Laravel (inspiration)
- Tailwind CSS
- Heroicons
- All contributors

**Built with ❤️ for the PHP community**
