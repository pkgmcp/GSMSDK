<?php

use GSMSDK\HTTP\Router;
use App\Controllers\Api\AuthController;
use GSMSDK\Middleware\ApiMiddleware;

return function (Router $router) {

    // ==============================
    // Authentication Routes
    // ==============================
    $router->group(['prefix' => '/auth', 'namespace' => 'App\Controllers\Api'], function ($router) {
        
        // Login
        $router->post('/login', [AuthController::class, 'login'])
            ->name('auth.login');
        
        // Register
        $router->post('/register', [AuthController::class, 'register'])
            ->name('auth.register');
        
        // Logout
        $router->post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth')
            ->name('auth.logout');
        
        // Refresh token
        $router->post('/refresh', [AuthController::class, 'refresh'])
            ->middleware('auth')
            ->name('auth.refresh');
        
        // Verify email
        $router->post('/verify', [AuthController::class, 'verify'])
            ->middleware('auth')
            ->name('auth.verify');
        
        // Forgot password
        $router->post('/forgot', [AuthController::class, 'forgot'])
            ->name('auth.forgot');
        
        // Reset password
        $router->post('/reset', [AuthController::class, 'reset'])
            ->name('auth.reset');
        
        // Check status
        $router->get('/check', [AuthController::class, 'check'])
            ->name('auth.check');
        
        // Me (current user)
        $router->get('/me', [AuthController::class, 'profile'])
            ->middleware('auth')
            ->name('auth.me');
        
        // Update profile
        $router->put('/me', [AuthController::class, 'update'])
            ->middleware('auth')
            ->name('auth.update');
        
        // Change password
        $router->put('/password', [AuthController::class, 'password'])
            ->middleware('auth')
            ->name('auth.password');
    });
    
    // ==============================
    // OAuth Routes
    // ==============================
    $router->group(['prefix' => '/oauth', 'namespace' => 'App\Controllers\Api'], function ($router) {
        $router->get('/redirect/{provider}', 'OAuthController@redirect')->name('oauth.redirect');
        $router->get('/callback/{provider}', 'OAuthController@callback')->name('oauth.callback');
    });
};
