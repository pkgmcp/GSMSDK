<?php
/**
 * GSMSDK Front Controller
 *
 * Entry point for all web requests
 * TheGridCN theme - Modern dark design
 */

use GSMSDK\Core\Application;
use GSMSDK\Core\MvcApplication;
use GSMSDK\Core\View;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize application
$app = new MvcApplication([
    'debug' => true,
    'environment' => 'development',
    'paths' => [
        'base' => dirname(__DIR__),
        'config' => dirname(__DIR__) . '/config',
        'views' => dirname(__DIR__) . '/resources/views',
        'controllers' => dirname(__DIR__) . '/app/Controllers',
        'models' => dirname(__DIR__) . '/app/Models',
        'storage' => dirname(__DIR__) . '/storage',
        'logs' => dirname(__DIR__) . '/storage/logs',
    ],
    'app' => [
        'name' => 'GSMSDK',
        'url' => 'http://localhost:8000',
        'timezone' => 'UTC',
        'locale' => 'en',
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'gsmsdk',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
]);

// Register service bindings
$app->bind('request', fn() => new Request());
$app->bind('response', fn() => new Response());
$app->bind('view', fn() => new View(
    $app->config('paths.views'),
    $app->config('paths.views') . '/layouts'
));

// Register middleware
$app->addMiddleware(function ($request, $response) {
    // CORS headers
    $response->header('Access-Control-Allow-Origin', '*')
             ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
             ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    
    if ($request->method() === 'OPTIONS') {
        $response->status(200)->send();
        return false;
    }
    
    // CSRF protection for POST/PUT/DELETE
    if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
        // Skip CSRF for API routes
        if (!str_starts_with($request->path(), '/api/')) {
            $token = $request->session('_token');
            if (!$token || $token !== $request->input('_token')) {
                $response->status(419)->json(['error' => 'CSRF token mismatch']);
                return false;
            }
        }
    }
    
    return true;
});

// Define routes
$app->get('/', 'HomeController@index');
$app->get('/dashboard', 'DashboardController@index');

// API routes
$app->get('/api/status', 'Api\StatusController@index');
$app->get('/api/devices', 'Api\DeviceController@index');
$app->post('/api/devices/{id}/shell', 'Api\DeviceController@shell');
$app->get('/api/fastboot/devices', 'Api\FastbootController@index');
$app->post('/api/fastboot/flash', 'Api\FastbootController@flash');

// Device routes
$app->get('/devices', 'DeviceController@index');
$app->get('/devices/{id}', 'DeviceController@show');
$app->post('/devices/{id}/install', 'DeviceController@install');
$app->post('/devices/{id}/shell', 'DeviceController@shell');

// Flash routes
$app->get('/flash', 'FlashController@index');
$app->post('/flash/partition', 'FlashController@partition');
$app->post('/flash/firmware', 'FlashController@firmware');

// Mobile app routes
$app->get('/mobile/app', 'Mobile\AppController@index');
$app->post('/mobile/app/generate', 'Mobile\AppController@generate');

// Desktop app routes
$app->get('/desktop/app', 'Desktop\AppController@index');
$app->post('/desktop/app/build', 'Desktop\AppController@build');

// Error handling
$app->addMiddleware(function ($request, $response) use ($app) {
    try {
        return true;
    } catch (\Throwable $e) {
        $app->handleException($e);
        return false;
    }
});

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (empty($_SESSION['_token'])) {
    $_SESSION['_token'] = bin2hex(random_bytes(32));
}

// Run application
$app->run();
