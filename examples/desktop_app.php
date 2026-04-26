<?php
/**
 * Desktop Application Example
 */

use GSMSDK\Desktop\Application as DesktopApp;
use GSMSDK\Desktop\Window;

require __DIR__ . '/../vendor/autoload.php';

// Create desktop application
$app = new DesktopApp([
    'debug' => true,
    'environment' => 'development',
]);

// Configure application window
$window = $app->createWindow([
    'title' => 'GSMSDK Desktop App',
    'width' => 1200,
    'height' => 800,
    'resizable' => true,
    'frame' => true,
    'background_color' => '#1a1a1e',
    'web_preferences' => [
        'node_integration' => true,
        'context_isolation' => true,
    ],
]);

// Run the application
$app->run();

echo "Desktop application initialized\n";
echo "Title: " . $window->getTitle() . "\n";
echo "Dimensions: " . json_encode($window->getDimensions()) . "\n";
