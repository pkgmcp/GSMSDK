<?php
/**
 * GSMSDK Web Application Entry Point
 */

use GSMSDK\Core\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application([
    'debug' => true,
    'environment' => 'development',
    'paths' => [
        'base' => dirname(__DIR__),
        'config' => dirname(__DIR__) . '/config',
        'storage' => dirname(__DIR__) . '/storage',
        'logs' => dirname(__DIR__) . '/storage/logs',
    ],
]);

echo $app . "\n";
echo "GSMSDK is running!\n";
