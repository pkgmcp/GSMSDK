<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use GSMSDK\Core\Controller;

class StatusController extends Controller
{
    public function index($request, $response): void
    {
        $status = [
            'status' => 'ok',
            'service' => 'GSMSDK API',
            'version' => $this->app->version(),
            'environment' => $this->app->environment(),
            'timestamp' => date('c'),
            'php_version' => PHP_VERSION,
        ];
        $response->json($status);
    }
}
