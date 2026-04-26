<?php
/**
 * GSMSDK Front Controller
 *
 * Entry point for all web requests using GSM templating engine
 */

use GSMSDK\Core\MvcApplication;
use GSMSDK\Core\View;

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
if (empty($_SESSION['_token'])) {
    $_SESSION['_token'] = bin2hex(random_bytes(32));
}

// Initialize application
$app = new MvcApplication([
    'debug' => true,
    'environment' => 'development',
    'paths' => [
        'base' => dirname(__DIR__),
        'views' => dirname(__DIR__) . '/resources/views',
        'controllers' => dirname(__DIR__) . '/app/Controllers',
        'layouts' => dirname(__DIR__) . '/resources/views/layouts',
    ],
    'app' => [
        'name' => 'GSMSDK',
        'url' => 'http://localhost:8000',
    ],
]);

// Define routes
$app->get('/', 'HomeController@index');
$app->get('/dashboard', function ($request, $response) {
    $data = [
        'title' => 'Dashboard',
        'version' => $response->app->version(),
        'features' => [
            'MVC Framework',
            'GSM Templating Engine',
            'ADB Integration',
            'Fastboot Integration',
            'Desktop Apps',
            'Mobile Apps',
        ],
        'devices' => [
            ['id' => 'emulator-5554', 'type' => 'emulator', 'state' => 'device'],
        ],
    ];
    $html = $response->app->view('home', $data);
    $response->status(200)
             ->header('Content-Type', 'text/html')
             ->body($html)
             ->send();
});

// API routes
$app->get('/api/status', 'Api\StatusController@index');
$app->get('/api/devices', 'DeviceController@index');
$app->post('/api/devices/{id}/shell', 'DeviceController@shell');
$app->post('/api/devices/{id}/install', 'DeviceController@install');

// Device routes
$app->get('/devices', function ($request, $response) {
    $data = [
        'title' => 'Devices',
        'devices' => [
            ['id' => 'emulator-5554', 'type' => 'emulator', 'state' => 'device'],
        ],
    ];
    $html = $response->app->view('home', $data);
    $response->json(['devices' => $data['devices']]);
});

// Flash routes
$app->get('/flash', function ($request, $response) {
    $data = [
        'title' => 'Flash Tool',
        'partitions' => ['boot', 'system', 'vendor', 'recovery'],
    ];
    $html = $response->app->view('home', $data);
    $response->status(200)->body($html)->send();
});

// Status check
$app->get('/check', function ($request, $response) {
    $response->json([
        'status' => 'ok',
        'version' => $response->app->version(),
        'env' => $response->app->environment(),
    ]);
});

// Start application
$app->run();
