<?php

declare(strict_types=1);

namespace App\Controllers;

use GSMSDK\Core\Controller;
use GSMSDK\ADB\ADBDevice;

/**
 * Device Controller
 */
class DeviceController extends Controller
{
    /**
     * List all devices
     */
    public function index($request, $response): void
    {
        // This would integrate with actual ADB in production
        $devices = [
            [
                'id' => 'emulator-5554',
                'model' => 'Android SDK built for x86',
                'state' => 'device',
                'type' => 'emulator',
            ],
        ];

        $response->json(['success' => true, 'devices' => $devices]);
    }

    /**
     * Show device details
     */
    public function show($request, $response): void
    {
        $id = $request->route('id');
        
        $device = [
            'id' => $id,
            'model' => 'Android SDK built for x86',
            'state' => 'device',
            'type' => 'emulator',
            'android_version' => '13.0',
            'api_level' => 33,
        ];

        $response->json(['success' => true, 'device' => $device]);
    }

    /**
     * Install APK on device
     */
    public function install($request, $response): void
    {
        $id = $request->route('id');
        $apkPath = $request->input('path');

        // In production: $adb = new ADBDevice(); $adb->install($apkPath);
        
        $response->json([
            'success' => true,
            'message' => 'APK installation initiated',
            'device' => $id,
            'apk' => $apkPath,
        ]);
    }

    /**
     * Execute shell command
     */
    public function shell($request, $response): void
    {
        $id = $request->route('id');
        $command = $request->input('command');

        // In production: $adb = new ADBDevice(); $output = $adb->shell($command);
        $output = "Simulated output for: {$command}";

        $response->json([
            'success' => true,
            'command' => $command,
            'output' => $output,
        ]);
    }
}
