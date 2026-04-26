<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use GSMSDK\Core\Application;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

/**
 * Device Controller
 *
 * Manages Android devices connected via ADB/Fastboot
 */
class DeviceController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * List all connected devices
     */
    public function index(Request $request): Response
    {
        // In production: Query actual devices
        $devices = [
            [
                'id' => 'emulator-5554',
                'model' => 'sdk_gphone64_x86_64',
                'state' => 'device',
                'type' => 'emulator',
                'product' => 'sdk_phone64_x86_64',
                'api_level' => 34,
            ],
        ];

        return Response::json(['devices' => $devices]);
    }

    /**
     * Get specific device details
     */
    public function show(Request $request, string $id): Response
    {
        return Response::json([
            'device' => [
                'id' => $id,
                'model' => 'sdk_gphone64_x86_64',
                'state' => 'device',
                'type' => 'emulator',
                'product' => 'sdk_phone64_x86_64',
                'api_level' => 34,
                'properties' => [
                    'ro.product.model' => 'sdk_gphone64_x86_64',
                    'ro.product.manufacturer' => 'Google',
                    'ro.build.version.release' => '14',
                ],
            ],
        ]);
    }

    /**
     * Register new device
     */
    public function store(Request $request): Response
    {
        try {
            $data = $request->validate([
                'serial' => 'required|string',
                'model' => 'required|string',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
                'details' => json_decode($e->getMessage(), true),
            ], 422);
        }

        // In production: Save to database
        return Response::json([
            'status' => 'success',
            'message' => 'Device registered',
            'device' => $data,
        ], 201);
    }

    /**
     * Update device information
     */
    public function update(Request $request, string $id): Response
    {
        try {
            $data = $request->validate([
                'model' => 'string',
                'notes' => 'string',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
                'details' => json_decode($e->getMessage(), true),
            ], 422);
        }

        return Response::json([
            'status' => 'success',
            'message' => 'Device updated',
        ]);
    }

    /**
     * Delete device
     */
    public function destroy(Request $request, string $id): Response
    {
        // In production: Remove from database
        return Response::json([
            'status' => 'success',
            'message' => 'Device deleted',
        ]);
    }

    /**
     * Execute shell command on device
     */
    public function shell(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        $command = $request->post('command', '');
        if (empty($command)) {
            return Response::json(['error' => 'Command required'], 400);
        }

        // In production: Execute via ADB
        // $output = $adb->shell($command);

        return Response::json([
            'status' => 'success',
            'command' => $command,
            'output' => [],
        ]);
    }

    /**
     * Install APK on device
     */
    public function install(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        $apkPath = $request->post('apk_path', '');
        if (empty($apkPath)) {
            return Response::json(['error' => 'APK path required'], 400);
        }

        // In production: Install via ADB
        return Response::json([
            'status' => 'success',
            'message' => 'APK installed successfully',
        ]);
    }

    /**
     * Reboot device
     */
    public function reboot(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        $mode = $request->post('mode', 'normal');

        return Response::json([
            'status' => 'success',
            'message' => "Rebooting device ($mode)",
        ]);
    }

    /**
     * Take screenshot
     */
    public function screenshot(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        return Response::json([
            'status' => 'success',
            'screenshot_url' => '/screenshots/' . $id . '.png',
        ]);
    }

    /**
     * Get logcat output
     */
    public function logcat(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        $lines = $request->get('lines', 1000);
        $filter = $request->get('filter', '');

        return Response::json([
            'status' => 'success',
            'logs' => [],
        ]);
    }

    /**
     * Push file to device
     */
    public function push(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        return Response::json([
            'status' => 'success',
            'message' => 'File pushed successfully',
        ]);
    }

    /**
     * Pull file from device
     */
    public function pull(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        return Response::json([
            'status' => 'success',
            'message' => 'File pulled successfully',
        ]);
    }

    /**
     * Get device partitions
     */
    public function partitions(Request $request, string $id): Response
    {
        $this->app->auth->middleware($request, 'auth');

        return Response::json([
            'partitions' => [
                ['name' => 'boot', 'type' => 'raw', 'size' => '32.0 MB', 'slot' => 'a'],
                ['name' => 'system', 'type' => 'ext4', 'size' => '2.1 GB', 'slot' => 'a'],
                ['name' => 'vendor', 'type' => 'ext4', 'size' => '1.4 GB', 'slot' => 'a'],
            ],
        ]);
    }
}
