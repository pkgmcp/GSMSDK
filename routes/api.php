<?php

use GSMSDK\HTTP\Router;
use App\Controllers\Api\ApiController;
use App\Controllers\Api\StatusController;
use App\Controllers\Api\DeviceController;
use App\Controllers\Api\Firmware\FirmwareController;
use GSMSDK\Middleware\ApiMiddleware;

return function (Router $router) {
    $router->group(['prefix' => '/api', 'middleware' => ['auth', 'throttle']], function ($router) {
        $router->get('/status', [StatusController::class, 'index'])->name('api.status');
        $router->get('/explorer', [ApiController::class, 'explorer'])->name('api.explorer');
        
        // Firmware Management
        $router->group(['prefix' => '/firmware'], function ($router) {
            $router->get('/', [FirmwareController::class, 'index']);
            $router->get('/{id}', [FirmwareController::class, 'show']);
            $router->get('/device/{brand}/{model}', [FirmwareController::class, 'forDevice']);
            $router->get('/latest/{brand}/{model}', [FirmwareController::class, 'latest']);
            $router->get('/search', [FirmwareController::class, 'search']);
            $router->get('/popular', [FirmwareController::class, 'popular']);
            $router->get('/recommended/{brand}/{model}', [FirmwareController::class, 'recommended']);
            $router->get('/download/{id}', [FirmwareController::class, 'download']);
            $router->post('/rate/{id}', [FirmwareController::class, 'rate']);
            $router->get('/brands', [FirmwareController::class, 'brands']);
            $router->get('/models/{brand}', [FirmwareController::class, 'models']);
            
            // Google/Pixel specific endpoints
            $router->get('/google/factory', [FirmwareController::class, 'googleFactoryImages']);
            $router->get('/google/ota', [FirmwareController::class, 'googleOtaUpdates']);
            $router->get('/google/models', [FirmwareController::class, 'googleModels']);
            
            // Lenovo specific endpoints
            $router->get('/lenovo/stock', [FirmwareController::class, 'lenovoStock']);
            $router->get('/lenovo/unbrick', [FirmwareController::class, 'lenovoUnbrick']);
            
            // OnePlus specific endpoints
            $router->get('/oneplus/unbrick', [FirmwareController::class, 'oneplusUnbrick']);
            $router->get('/oneplus/stock', [FirmwareController::class, 'oneplusStock']);
            
            // Admin routes
            $router->post('/', [FirmwareController::class, 'create']);
            $router->put('/{id}', [FirmwareController::class, 'update']);
            $router->delete('/{id}', [FirmwareController::class, 'delete']);
        });
        
        $router->group(['prefix' => '/devices'], function ($router) {
            $router->get('/', [DeviceController::class, 'index']);
            $router->get('/{id}', [DeviceController::class, 'show']);
            $router->post('/connect', [DeviceController::class, 'connect']);
            $router->post('/{id}/disconnect', [DeviceController::class, 'disconnect']);
            $router->post('/{id}/shell', [DeviceController::class, 'shell']);
            $router->post('/{id}/reboot', [DeviceController::class, 'reboot']);
            $router->post('/flash', [DeviceController::class, 'flash']);
            $router->get('/partitions', [DeviceController::class, 'partitions']);
            $router->get('/{id}/status', [DeviceController::class, 'status']);
            $router->get('/usb/status', [DeviceController::class, 'usbStatus']);
        });
        
        // Samsung Download Mode
        $router->group(['prefix' => '/samsung'], function ($router) {
            $router->post('/download', [DeviceController::class, 'samsungDownload']);
            $router->post('/flash', [DeviceController::class, 'flashSamsung']);
        });
    });
};
