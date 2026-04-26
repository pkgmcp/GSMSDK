<?php
/**
 * Laravel 13-Style Routing Example for GSMSDK
 *
 * Demonstrates modern routing patterns including:
 * - Route groups with middleware, prefix, namespace
 * - Resource controllers
 * - API resources
 * - Named routes
 * - Model binding
 * - Route parameters with constraints
 * - Route caching
 */

use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;
use GSMSDK\HTTP\Router;
use GSMSDK\Core\Application;

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize app
$app = new Application([
    'environment' => 'development',
    'paths' => [
        'base' => dirname(__DIR__),
        'views' => dirname(__DIR__) . '/resources/views',
    ],
]);

// Create router
$router = new Router();

// ==============================
// Global middleware
// ==============================
$router->addGlobalMiddleware('CSRF');
$router->addGlobalMiddleware('CORS');

// ==============================
// Web Routes
// ==============================
$router->group(['namespace' => 'App\Controllers\Web', 'middleware' => ['auth', 'log']], function ($router) {
    
    // Home route (closure)
    $router->get('/', function (Request $request) {
        return new Response('<h1>Welcome to GSMSDK</h1>');
    })->name('home');
    
    // Dashboard (controller action)
    $router->get('/dashboard', 'DashboardController@index')->name('dashboard');
    
    // Named route with parameter
    $router->get('/users/{id}', 'UserController@show')->name('users.show');
    
    // Route with regex constraint
    $router->get('/posts/{id:\d+}', 'PostController@show')->name('posts.show');
    
    // Route with optional parameter
    $router->get('/pages/{slug?}', 'PageController@show')->name('pages.show');
    
    // Form routes
    $router->get('/users/create', 'UserController@create')->name('users.create');
    $router->post('/users', 'UserController@store')->name('users.store');
    
    // Group for authenticated profile routes
    $router->group(['prefix' => '/profile', 'name' => 'profile.'], function ($router) {
        $router->get('/', 'ProfileController@index')->name('index');
        $router->get('/edit', 'ProfileController@edit')->name('edit');
        $router->put('/', 'ProfileController@update')->name('update');
    });
});

// ==============================
// API Routes (stateless)
// ==============================
$router->group(['prefix' => '/api', 'namespace' => 'App\Controllers\Api', 'middleware' => ['api.throttle']], function ($router) {
    
    // API resource (excludes create/edit)
    $router->apiResource('/devices', 'DeviceController');
    
    // Full resource
    $router->resource('/users', 'UserController');
    
    // Custom API endpoints
    $router->get('/status', 'StatusController@index')->name('api.status');
    $router->post('/devices/{id}/flash', 'DeviceController@flash')->name('devices.flash');
});

// ==============================
// Redirect Routes
// ==============================
$router->redirect('/old-path', '/new-path', 301);
$router->redirect('/docs', 'https://docs.gsmsdk.io');

// ==============================
// View Routes
// ==============================
$router->view('/about', 'pages.about', ['title' => 'About GSMSDK'], 'about');

// ==============================
// Fallback/404 Route
// ==============================
$router->any('/{any:.*}', function () {
    return new Response('<h1>404 - Page Not Found</h1>', 404);
});

// ==============================
// Model Binding
// ==============================
$router->model('device', 'App\Models\Device', function ($id) {
    return \App\Models\Device::find($id) ?? abort(404);
});

$router->model('user', 'App\Models\User');

// ==============================
// Route Caching (for production)
// ==============================
// $router->cache(__DIR__ . '/../storage/routes.cache');
// Or load cached routes:
// $router->loadCache(__DIR__ . '/../storage/routes.cache');

// ==============================
// Generate URLs using named routes
// ==============================
try {
    $url = $router->route('users.show', ['id' => 123]);
    echo "URL for users.show: {$url}\n"; // /users/123
    
    $url = $router->route('devices.flash', ['id' => 456]);
    echo "URL for devices.flash: {$url}\n"; // /api/devices/456/flash
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ==============================
// Dispatch Request
// ==============================
$request = new Request();

try {
    $response = $router->dispatch($request->getMethod(), $request->getUri(), $request);
    $response->send();
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());
    
    // Return 500 error
    $response = new Response('<h1>500 - Server Error</h1>', 500);
    $response->send();
}

// ==============================
// Example Controller
// ==============================
class UserController
{
    public function index(Request $request)
    {
        return new Response('<h1>User List</h1>');
    }
    
    public function show(Request $request, int $id)
    {
        return new Response("<h1>User Profile: {$id}</h1>");
    }
    
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|min:3|max:50',
            'email' => 'required|email',
            'age' => 'integer|min:18',
        ]);
        
        return Response::json(['message' => 'User created', 'data' => $validated]);
    }
    
    public function update(Request $request, int $id)
    {
        // Route parameter available via $request->route('id')
        return Response::json(['message' => "Updated user {$id}"]);
    }
    
    public function destroy(int $id)
    {
        return Response::json(['message' => "Deleted user {$id}"]);
    }
}

// ==============================
// Example Middleware
// ==============================
namespace App\Middleware;

class Auth
{
    public function handle(Request $request)
    {
        if (!isset($_SESSION['user'])) {
            return new Response('<h1>401 - Unauthorized</h1>', 401);
        }
        return null; // Continue to route
    }
}

class CSRF
{
    public function handle(Request $request)
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->post('_token') ?? $request->header('X-CSRF-Token');
            if ($token !== ($_SESSION['_token'] ?? '')) {
                return new Response('<h1>419 - CSRF Token Mismatch</h1>', 419);
            }
        }
        return null;
    }
}

echo "\nLaravel-style routing example complete!\n";
