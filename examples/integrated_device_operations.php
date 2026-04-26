<?php
/**
 * Integrated ADB + Fastboot Device Operations Example
 * 
 * Demonstrates using GSMSDK's unified DeviceManager to perform
 * complete device lifecycle operations.
 */

use GSMSDK\DeviceManager;
use GSMSDK\Mobile\App as MobileApp;

require __DIR__ . '/../vendor/autoload.php';

echo "GSMSDK Integrated Device Operations\n";
echo "====================================\n\n";

// Initialize device manager
$manager = new DeviceManager([
    'adb' => [
        'host' => '127.0.0.1',
        'port' => 5037,
    ],
    'fastboot' => [
        'host' => '127.0.0.1',
        'port' => 5556,
    ],
]);

// Create mobile app configuration
$app = new MobileApp([
    'name' => 'GSMSDK Demo App',
    'identifier' => 'io.gsmsdk.demo',
    'version' => '1.0.0',
    'build' => '1',
]);
$app->addPlatform('android');
$app->addPermission('android.permission.INTERNET');
$app->addPermission('android.permission.ACCESS_NETWORK_STATE');

echo "App Configuration:\n";
echo "  Name: " . $app->getName() . "\n";
echo "  Package: " . $app->getIdentifier() . "\n";
echo "  Version: " . $app->getVersion() . "\n";
echo "\n";

// Example: Complete device flash workflow
function flashCompleteDevice(DeviceManager $manager, string $serial) {
    echo "Step 1: Connect via ADB\n";
    try {
        $adb = $manager->connectADB($serial);
        echo "  ✓ Connected to device\n";
        
        // Get device info
        $props = $adb->getProperties();
        echo "  ✓ Device: " . ($props['ro.product.model'] ?? 'Unknown') . "\n";
        echo "  ✓ Android: " . ($props['ro.build.version.release'] ?? 'Unknown') . "\n";
    } catch (\Throwable $e) {
        echo "  ✗ ADB Connection failed: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "\nStep 2: Reboot to bootloader\n";
    try {
        $adb->reboot('bootloader');
        echo "  ✓ Rebooting to bootloader...\n";
        sleep(5); // Wait for reboot
    } catch (\Throwable $e) {
        echo "  ✗ Reboot failed: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "\nStep 3: Switch to Fastboot mode\n";
    try {
        $manager->switchToFastboot();
        $fastboot = $manager->getFastbootDevice();
        echo "  ✓ Connected in Fastboot mode\n";
        
        // Check device variables
        $product = $fastboot->getVariable('product');
        echo "  ✓ Product: " . ($product ?? 'Unknown') . "\n";
        
        $unlocked = $fastboot->getVariable('unlocked');
        echo "  ✓ Bootloader: " . ($unlocked === 'yes' ? 'Unlocked' : 'Locked') . "\n";
    } catch (\Throwable $e) {
        echo "  ✗ Fastboot connection failed: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "\nStep 4: Flash partitions\n";
    $partitions = [
        'boot' => '/path/to/boot.img',
        'system' => '/path/to/system.img',
        'vendor' => '/path/to/vendor.img',
    ];
    
    foreach ($partitions as $part => $path) {
        echo "  Flashing $part...\n";
        try {
            // In real usage, ensure paths exist
            // $fastboot->flash($part, $path);
            echo "    ✓ $part flashed\n";
        } catch (\Throwable $e) {
            echo "    ✗ Failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nStep 5: Reboot device\n";
    try {
        $fastboot->reboot();
        echo "  ✓ Device rebooting...\n";
    } catch (\Throwable $e) {
        echo "  ✗ Reboot failed: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "\nStep 6: Wait for device to boot\n";
    sleep(10);
    
    echo "\nStep 7: Reconnect via ADB\n";
    try {
        $adb = $manager->connectADB($serial);
        echo "  ✓ Device online\n";
        
        // Install app
        echo "\nStep 8: Install application\n";
        // $adb->install('/path/to/app.apk');
        echo "  ✓ App installed\n";
        
        // Run shell command
        echo "\nStep 9: Verify installation\n";
        $output = $adb->shell('pm list packages | grep gsmsdk');
        echo "  ✓ Package verified\n";
        
    } catch (\Throwable $e) {
        echo "  ✗ ADB connection failed: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "Device flash complete!\n";
    echo str_repeat("=", 40) . "\n";
    
    return true;
}

// Example: Quick ADB operations
function quickADBOperations(DeviceManager $manager, string $serial) {
    echo "Quick ADB Operations\n";
    echo "-------------------\n\n";
    
    try {
        $adb = $manager->connectADB($serial);
        
        // Get device info
        $props = $adb->getProperties();
        echo "Device: " . ($props['ro.product.model'] ?? 'Unknown') . "\n";
        echo "Android: " . ($props['ro.build.version.release'] ?? 'Unknown') . "\n";
        
        // Get device state
        $output = $adb->shell('getprop ro.build.display.id');
        echo "Build: " . trim($output) . "\n\n";
        
        // List packages
        echo "Sample packages (first 5):\n";
        $output = $adb->shell('pm list packages | head -5');
        echo $output . "\n";
        
        // Storage info
        echo "Storage:\n";
        $output = $adb->shell('df -h /sdcard 2>/dev/null || df -h /storage');
        echo $output . "\n";
        
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Example: Fastboot info
function fastbootInfo(DeviceManager $manager, string $serial) {
    echo "Fastboot Device Info\n";
    echo "-------------------\n\n";
    
    try {
        $fastboot = $manager->connectFastboot($serial);
        
        $vars = $fastboot->getAllVariables();
        foreach ($vars as $name => $value) {
            echo sprintf("  %-25s %s\n", $name . ":", $value ?? '(null)');
        }
        
        echo "\nDevice status: " . ($fastboot->isConnected() ? "Connected" : "Disconnected") . "\n";
        
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "(Note: Requires fastboot-php library)\n";
    }
}

// Usage examples (serial would be actual device serial)
$serial = 'emulator-5554'; // Example serial

echo "Example 1: Quick ADB Operations\n";
echo "(" . "Note: Replace with actual device serial\n)\n";
// quickADBOperations($manager, $serial);

echo "\nExample 2: Fastboot Info\n";
echo "(" . "Note: Device must be in fastboot mode\n)\n";
// fastbootInfo($manager, $serial);

echo "\nExample 3: Complete Flash Workflow\n";
echo "(" . "Note: Requires actual device and image files\n)\n";
// flashCompleteDevice($manager, $serial);

echo "\nTo run with actual device:\n";
echo "  php examples/integrated_device_operations.php\n";
echo "\nMake sure to:\n";
echo "  1. Connect your Android device\n";
echo "  2. Enable USB debugging\n";
echo "  3. Install adb-php and fastboot-php:\n";
echo "     composer require pkgmcp/adb-php pkgmcp/fastboot-php\n";
