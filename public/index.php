<?php

/**
 * GSMSDK Front Controller
 *
 * Entry point for all web requests using GSM templating engine
 */

use GSMSDK\Core\MvcApplication;

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
$app->routeGet('/', 'HomeController@index');
$app->routeGet('/dashboard', function ($request, $response) {
    $data = [
        'title' => 'Dashboard',
        'version' => '3.0.0',
        'features' => [
            'MVC Framework',
            'GSM Templating Engine',
            'ADB Integration',
            'Fastboot Support',
            'Premium Upload System',
            'Subscription Management'
        ]
    ];
    return $response->view('admin/dashboard', $data);
});

$app->routeGet('/upload', function ($request, $response) {
    $data = [
        'title' => 'Premium Upload Center',
        'totalFiles' => 156,
        'totalDownloads' => 15420,
        'premiumUsers' => 892,
        'revenue' => 45230,
        'files' => [
            ['name' => 'firmware_xiaomi_redmi.zip', 'size' => '245 MB', 'category' => 'firmware', 'downloads' => 1240],
            ['name' => 'tool_fastboot_util.zip', 'size' => '12 MB', 'category' => 'tools', 'downloads' => 856],
            ['name' => 'guide_imei_repair.pdf', 'size' => '3 MB', 'category' => 'documentation', 'downloads' => 2341],
        ]
    ];
    return $response->view('upload/index', $data);
});

$app->routeGet('/firmware', function ($request, $response) {
    return $response->view('admin/firmware', ['title' => 'Firmware Management']);
});

$app->routeGet('/flash', function ($request, $response) {
    return $response->view('flash/index', ['title' => 'Flash Tool']);
});

$app->routeGet('/imei-checker', function ($request, $response) {
    return $response->view('flash/adb', ['title' => 'IMEI Checker']);
});

$app->routeGet('/signin', function ($request, $response) {
    $data = ['title' => 'Sign In'];
    return $response->view('home', $data);
});

$app->routeGet('/signup', function ($request, $response) {
    $data = ['title' => 'Sign Up'];
    return $response->view('home', $data);
});

// Run application
$app->run();
