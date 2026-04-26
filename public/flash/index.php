<?php
/**
 * GSMSDK Flash Tool - Front Controller
 *
 * Full-stack Android firmware flashing interface
 */

declare(strict_types=1);

use GSMSDK\Core\MvcApplication;
use App\Controllers\FlashController;

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
        'layouts' => dirname(__DIR__) . '/resources/views/layouts',
    ],
    'app' => [
        'name' => 'GSMSDK Flash Tool',
        'url' => 'http://localhost:8000/flash',
    ],
]);

// Flash controller
$flash = new FlashController($app);

// Routes
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path
$uri = str_replace('/flash', '', $uri);
if ($uri === '') {
    $uri = '/';
}

switch ($uri) {
    case '/':
    case '/dashboard':
        $flash->dashboard();
        break;
    case '/flash':
        $flash->index();
        break;
    case '/devices':
        $flash->devices();
        break;
    case '/adb':
        $flash->adb();
        break;
    case '/terminal':
        $flash->terminal();
        break;
    case '/logs':
        $flash->logs();
        break;
    case '/files':
        $flash->files();
        break;
    case '/api/devices':
        $flash->apiDevices();
        break;
    case '/api/partitions':
        $flash->apiPartitions();
        break;
    case '/api/flash':
        $flash->apiFlash();
        break;
    default:
        http_response_code(404);
        echo $app->view('flash/404', []);
        break;
}
