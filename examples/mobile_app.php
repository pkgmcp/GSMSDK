<?php
/**
 * Mobile Application Example
 */

use GSMSDK\Mobile\App as MobileApp;

require __DIR__ . '/../vendor/autoload.php';

// Create mobile application
$app = new MobileApp([
    'name' => 'My GSMSDK App',
    'identifier' => 'io.gsmsdk.myapp',
    'version' => '1.0.0',
    'build' => '1',
]);

// Configure platforms
$app->addPlatform('android');
$app->addPlatform('ios');

// Add permissions
$app->addPermission('android.permission.INTERNET');
$app->addPermission('android.permission.ACCESS_NETWORK_STATE');

// Add capabilities
$app->addCapability('push_notifications');
$app->addCapability('offline_storage');

// Generate platform config files
echo "Android Manifest:\n";
echo $app->generateAndroidManifest();
echo "\n";

echo "iOS Info.plist:\n";
echo $app->generateInfoPlist();
echo "\n";

// Display app info
echo "App Name: " . $app->getName() . "\n";
echo "Bundle ID: " . $app->getIdentifier() . "\n";
echo "Version: " . $app->getVersion() . "\n";
echo "Platforms: " . implode(', ', $app->getPlatforms()) . "\n";
