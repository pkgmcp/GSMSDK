<?php

use GSMSDK\HTTP\Router;
use App\Controllers\Api\ApiController;
use App\Controllers\Api\StatusController;
use App\Controllers\Api\DeviceController;
use App\Controllers\Api\FlashController as ApiFlashController;
use App\Controllers\Api\AuthController;

return function (Router $router) {

    // ==============================
    // Public API Routes
    // ==============================
    $router->group(['prefix' => '/api', 'namespace' => 'App\Controllers\Api'], function ($router) {
        
        // Health & Status
        $router->get('/health', [ApiController::class, 'health'])->name('api.health');
        $router->get('/status', [ApiController::class, 'status'])->name('api.status');
        
        // API Documentation
        $router->get('/docs', [ApiController::class, 'docs'])->name('api.docs');
        $router->get('/docs/explorer', [ApiController::class, 'explorer'])->name('api.explorer');
        $router->get('/docs/openapi.json', [ApiController::class, 'docs']);
        
        // Endpoint Listing
        $router->get('/', [ApiController::class, 'index'])->name('api.index');
        
        // Authentication
        $router->post('/auth/login', [AuthController::class, 'login'])->name('api.login');
        $router->post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
        $router->post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('auth');
        $router->post('/auth/register', [AuthController::class, 'register']);
        $router->get('/auth/me', [AuthController::class, 'profile'])->middleware('auth');
        
        // Metrics (authenticated)
        $router->get('/metrics', [ApiController::class, 'metrics'])->middleware('auth');
        
        // ==============================
        // Device Management (authenticated)
        // ==============================
        $router->group(['prefix' => '/devices', 'middleware' => ['auth']], function ($router) {
            $router->get('/', [DeviceController::class, 'index'])->name('api.devices.index');
            $router->get('/{id}', [DeviceController::class, 'show'])->name('api.devices.show');
            $router->post('/', [DeviceController::class, 'store'])->name('api.devices.store');
            $router->put('/{id}', [DeviceController::class, 'update'])->name('api.devices.update');
            $router->delete('/{id}', [DeviceController::class, 'destroy'])->name('api.devices.destroy');
            $router->post('/{id}/shell', [DeviceController::class, 'shell'])->name('api.devices.shell');
            $router->post('/{id}/install', [DeviceController::class, 'install'])->name('api.devices.install');
            $router->post('/{id}/reboot', [DeviceController::class, 'reboot'])->name('api.devices.reboot');
            $router->post('/{id}/screencap', [DeviceController::class, 'screenshot'])->name('api.devices.screenshot');
        });
        
        // ==============================
        // Flash Operations (authenticated)
        // ==============================
        $router->group(['prefix' => '/flash', 'middleware' => ['auth']], function ($router) {
            $router->get('/', [ApiFlashController::class, 'index'])->name('api.flash.index');
            $router->post('/', [ApiFlashController::class, 'store'])->name('api.flash.store');
            $router->post('/{id}/execute', [ApiFlashController::class, 'execute'])->name('api.flash.execute');
            $router->post('/{id}/verify', [ApiFlashController::class, 'verify'])->name('api.flash.verify');
            $router->get('/history', [ApiFlashController::class, 'history'])->name('api.flash.history');
        });
        
        // ==============================
        // ADB Operations (authenticated)
        // ==============================
        $router->group(['prefix' => '/adb', 'middleware' => ['auth']], function ($router) {
            $router->post('/shell', [DeviceController::class, 'shell'])->name('api.adb.shell');
            $router->post('/install', [DeviceController::class, 'install'])->name('api.adb.install');
            $router->post('/push', [DeviceController::class, 'push'])->name('api.adb.push');
            $router->post('/pull', [DeviceController::class, 'pull'])->name('api.adb.pull');
            $router->post('/logcat', [DeviceController::class, 'logcat'])->name('api.adb.logcat');
        });
        
        // ==============================
        // AI Suggestions
        // ==============================
        $router->get('/suggest', [ApiController::class, 'suggest'])->name('api.suggest');
        
        // ==============================
        // Catch-all for unknown routes
        // ==============================
        $router->any('/{any:.*}', function () {
            return Response::json(['error' => 'Endpoint not found'], 404);
        });
    });
    
    // ==============================
    // Web API Routes (for AJAX from web interface)
    // ==============================
    $router->group(['prefix' => '/ajax', 'namespace' => 'App\Controllers\Api'], function ($router) {
        $router->get('/devices', [DeviceController::class, 'index'])->name('ajax.devices');
        $router->post('/devices/{id}/shell', [DeviceController::class, 'shell']);
        $router->post('/flash', [ApiFlashController::class, 'store']);
        $router->get('/partitions', [DeviceController::class, 'partitions'])->name('ajax.partitions');
    });
};
