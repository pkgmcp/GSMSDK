<?php

use GSMSDK\HTTP\Router;
use App\Controllers\Api\ApiController;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\DeviceController;
use App\Controllers\Api\FlashController as ApiFlashController;

return function (Router $router) {

    // ==============================
    // Web Routes
    // ==============================
    $router->group(['namespace' => 'App\Controllers\Web'], function ($router) {
        
        // Home
        $router->get('/', [ApiController::class, 'index'])->name('home');
        
        // Dashboard
        $router->get('/dashboard', function () {
            return (new \GSMSDK\Core\Application())->view('admin/dashboard');
        })->name('dashboard');
        
        // Devices
        $router->get('/devices', [DeviceController::class, 'index'])->name('devices');
        
        // Flash
        $router->get('/flash', [ApiFlashController::class, 'index'])->name('flash');
        
        // Terminal
        $router->get('/terminal', function () {
            return (new \GSMSDK\Core\Application())->view('flash/terminal');
        })->name('terminal');
        
        // Logs
        $router->get('/logs', [ApiFlashController::class, 'index'])->name('logs');
        
        // Files
        $router->get('/files', function () {
            return (new \GSMSDK\Core\Application())->view('flash/files');
        })->name('files');
        
        // Profile
        $router->get('/profile', [AuthController::class, 'profile'])->name('profile')->middleware('auth');
        
        // Settings
        $router->get('/settings', function () {
            return (new \GSMSDK\Core\Application())->view('admin/dashboard');
        })->name('settings')->middleware('auth');
    });
    
    // ==============================
    // Authentication Routes (Web)
    // ==============================
    $router->group(['namespace' => 'App\Controllers\Api'], function ($router) {
        
        // Login
        $router->get('/login', function () {
            if ((new \GSMSDK\Core\Auth\AuthManager(new \GSMSDK\Core\Application()))->check()) {
                return (new \GSMSDK\HTTP\Response())->redirect('/dashboard');
            }
            return (new \GSMSDK\Core\Application())->view('api/explorer');
        })->name('login');
        
        $router->post('/login', [AuthController::class, 'login'])->name('login.post');
        
        // Logout
        $router->post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Register
        $router->get('/register', function () {
            return (new \GSMSDK\Core\Application())->view('api/explorer');
        })->name('register');
        
        $router->post('/register', [AuthController::class, 'register'])->name('register.post');
        
        // Password Reset
        $router->get('/forgot-password', function () {
            return (new \GSMSDK\Core\Application())->view('api/explorer');
        })->name('password.request');
        
        $router->post('/forgot-password', [AuthController::class, 'forgot'])->name('password.email');
        
        $router->get('/reset-password/{token}', function ($token) {
            return (new \GSMSDK\Core\Application())->view('api/explorer');
        })->name('password.reset');
        
        $router->post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
        
        // Email Verification
        $router->get('/email/verify', function () {
            return (new \GSMSDK\Core\Application())->view('api/explorer');
        })->name('verification.notice');
        
        $router->post('/email/verify', [AuthController::class, 'verify'])->name('verification.verify');
        
        $router->post('/email/resend', [AuthController::class, 'verify'])->name('verification.resend');
    });
    
    // ==============================
    // API Routes (Web Access)
    // ==============================
    $router->group(['prefix' => '/api', 'namespace' => 'App\Controllers\Api'], function ($router) {
        
        // API Status
        $router->get('/status', [ApiController::class, 'status'])->name('api.status');
        
        // Health Check
        $router->get('/health', [ApiController::class, 'health'])->name('api.health');
        
        // API Explorer
        $router->get('/explorer', [ApiController::class, 'explorer'])->name('api.explorer');
        
        // Docs
        $router->get('/docs', [ApiController::class, 'docs'])->name('api.docs');
        
        // Metrics (authenticated)
        $router->get('/metrics', [ApiController::class, 'metrics'])->name('api.metrics')->middleware('auth');
        
        // Suggestions
        $router->get('/suggest', [ApiController::class, 'suggest'])->name('api.suggest');
    });
    
    // ==============================
    // Device Routes (Web)
    // ==============================
    $router->group(['prefix' => '/devices', 'namespace' => 'App\Controllers\Api', 'middleware' => ['auth']], function ($router) {
        
        $router->get('/', [DeviceController::class, 'index'])->name('devices.list');
        $router->get('/{id}', [DeviceController::class, 'show'])->name('devices.show');
        $router->post('/', [DeviceController::class, 'store'])->name('devices.store');
        $router->put('/{id}', [DeviceController::class, 'update'])->name('devices.update');
        $router->delete('/{id}', [DeviceController::class, 'destroy'])->name('devices.destroy');
        
        // ADB Commands
        $router->post('/{id}/shell', [DeviceController::class, 'shell'])->name('devices.shell');
        $router->post('/{id}/install', [DeviceController::class, 'install'])->name('devices.install');
        $router->post('/{id}/reboot', [DeviceController::class, 'reboot'])->name('devices.reboot');
        $router->post('/{id}/screencap', [DeviceController::class, 'screenshot'])->name('devices.screenshot');
        $router->post('/{id}/logcat', [DeviceController::class, 'logcat'])->name('devices.logcat');
    });
    
    // ==============================
    // Flash Routes (Web)
    // ==============================
    $router->group(['prefix' => '/flash', 'namespace' => 'App\Controllers\Api', 'middleware' => ['auth']], function ($router) {
        
        $router->get('/', [ApiFlashController::class, 'index'])->name('flash.index');
        $router->post('/', [ApiFlashController::class, 'store'])->name('flash.store');
        $router->post('/{id}/execute', [ApiFlashController::class, 'execute'])->name('flash.execute');
        $router->post('/{id}/verify', [ApiFlashController::class, 'verify'])->name('flash.verify');
        $router->get('/history', [ApiFlashController::class, 'history'])->name('flash.history');
    });
    
    // ==============================
    // ADB Routes (Web)
    // ==============================
    $router->group(['prefix' => '/adb', 'namespace' => 'App\Controllers\Api', 'middleware' => ['auth']], function ($router) {
        
        $router->post('/shell', [DeviceController::class, 'shell'])->name('adb.shell');
        $router->post('/install', [DeviceController::class, 'install'])->name('adb.install');
        $router->post('/push', [DeviceController::class, 'push'])->name('adb.push');
        $router->post('/pull', [DeviceController::class, 'pull'])->name('adb.pull');
        $router->post('/logcat', [DeviceController::class, 'logcat'])->name('adb.logcat');
    });
    
    // ==============================
    // AJAX Routes (for dynamic content)
    // ==============================
    $router->group(['prefix' => '/ajax', 'namespace' => 'App\Controllers\Api'], function ($router) {
        
        $router->get('/devices', [DeviceController::class, 'index'])->name('ajax.devices');
        $router->post('/devices/{id}/shell', [DeviceController::class, 'shell'])->name('ajax.devices.shell');
        $router->post('/flash', [ApiFlashController::class, 'store'])->name('ajax.flash');
        $router->get('/partitions', [DeviceController::class, 'partitions'])->name('ajax.partitions');
        $router->get('/status', [ApiController::class, 'status'])->name('ajax.status');
    });
    
    // ==============================
    // Catch-all Route (404)
    // ==============================
    $router->any('/{any:.*}', function () {
        return (new \GSMSDK\HTTP\Response())
            ->status(404)
            ->body((new \GSMSDK\Core\Application())->view('api/explorer'));
    });
};
