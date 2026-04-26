# Laravel 13-Style Routing

GSMSDK includes a modern router inspired by Laravel 13 with advanced features.

## Quick Example

```php
use GSMSDK\HTTP\Router;

$router = new Router();

// Named routes
$router->get('/', 'HomeController@index')->name('home');

// Route parameters with constraints
$router->get('/users/{id:\d+}', 'UserController@show')->name('users.show');

// Optional parameters
$router->get('/pages/{slug?}', 'PageController@show');

// Resource controllers (RESTful)
$router->resource('/posts', 'PostController');

// API resources (no create/edit)
$router->apiResource('/api/posts', 'PostController');

// Route groups with middleware, prefix, namespace
$router->group(['prefix' => '/admin', 'namespace' => 'Admin', 'middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', 'DashboardController@index');
    $router->resource('/users', 'UserController');
});

// Redirect routes
$router->redirect('/old', '/new', 301);

// View routes
$router->view('/about', 'pages.about', ['title' => 'About'], 'about');

// Model binding
$router->model('user', 'App\Models\User');

// Generate URLs from named routes
$url = $router->route('users.show', ['id' => 123]); // /users/123
```

## Available Methods

- `get($uri, $handler, $name = '')` - GET route
- `post($uri, $handler, $name = '')` - POST route
- `put($uri, $handler, $name = '')` - PUT route
- `patch($uri, $handler, $name = '')` - PATCH route
- `delete($uri, $handler, $name = '')` - DELETE route
- `match($methods, $uri, $handler, $name = '')` - Multiple methods
- `any($uri, $handler, $name = '')` - All methods
- `redirect($uri, $destination, $status)` - Redirect route
- `view($uri, $view, $data, $name)` - View route
- `resource($uri, $controller, $options)` - RESTful resource
- `apiResource($uri, $controller, $options)` - API resource

## Route Parameters

```php
// Required
$router->get('/users/{id}', 'UserController@show');

// Optional
$router->get('/users/{id?}', 'UserController@show');

// With regex constraint
$router->get('/posts/{id:\d+}', 'PostController@show');
```

## Route Groups

```php
$router->group(['prefix' => '/admin', 'namespace' => 'Admin', 'middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', 'DashboardController@index');
    $router->resource('/users', 'UserController');
});
```

Group options:
- `prefix` - URL prefix
- `namespace` - Controller namespace
- `middleware` - Middleware array
- `name` - Route name prefix

## Named Routes

```php
$router->get('/users/{id}', 'UserController@show')->name('users.show');

$url = $router->route('users.show', ['id' => 123]);
```

## Resource Controllers

```php
// Full resource
$router->resource('/posts', 'PostController');

// API resource (excludes create/edit)
$router->apiResource('/posts', 'PostController');

// Only specific methods
$router->resource('/posts', 'PostController', ['only' => ['index', 'show']]);

// Except specific methods
$router->resource('/posts', 'PostController', ['except' => ['destroy']]);
```

## Model Binding

```php
// Default resolver
$router->model('user', 'App\Models\User');

// Custom resolver
$router->model('device', 'App\Models\Device', function ($id) {
    return Device::where('serial', $id)->firstOrFail();
});
```

## Middleware

```php
// Global middleware
$router->addGlobalMiddleware('CSRF');

// Route middleware
$router->get('/admin', 'AdminController@index')->middleware('auth');

// Group middleware
$router->group(['middleware' => ['auth', 'log']], function ($router) {
    // All routes here use auth and log middleware
});
```

## Route Caching

```php
// Cache routes
$router->cache('storage/routes.cache');

// Load cached routes
$router->loadCache('storage/routes.cache');
```

## Full Example

See `examples/LaravelStyleRouting.php` for complete examples.
