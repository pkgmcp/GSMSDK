<?php

declare(strict_types=1);

namespace App\Controllers;

use GSMSDK\Core\Controller;
use GSMSDK\Core\View;

/**
 * Home Controller
 */
class HomeController extends Controller
{
    /**
     * Index page
     */
    public function index($request, $response): void
    {
        $data = [
            'title' => 'Welcome to GSMSDK',
            'version' => $this->app->version(),
            'environment' => $this->app->environment(),
            'features' => [
                'Dependency Injection Container',
                'HTTP Request/Response Layer',
                'Fluent Database Query Builder',
                'Desktop Application Support',
                'Mobile Application Config',
                'ADB & Fastboot Integration',
                'CLI Console Commands',
                'Type-Safe PHP 8.5+',
            ],
        ];

        $html = $this->app->view('home', $data);
        $response->status(200)
                 ->header('Content-Type', 'text/html')
                 ->body($html)
                 ->send();
    }
}
