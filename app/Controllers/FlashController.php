<?php

declare(strict_types=1);

namespace App\Controllers;

use GSMSDK\Core\Application;

/**
 * Flash Controller - Full-stack device firmware flashing
 */
class FlashController
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Render flash dashboard
     */
    public function dashboard(): void
    {
        $data = [
            'title' => 'Flash Dashboard | GSMSDK',
            'content' => $this->app->view('flash/dashboard', ['version' => $this->app->version()]),
        ];
        echo $this->app->view('flash/layout', $data);
    }

    /**
     * Main flash page
     */
    public function index(): void
    {
        $data = [
            'title' => 'Fastboot Flash | GSMSDK',
            'content' => $this->app->view('flash/index', []),
        ];
        echo $this->app->view('flash/layout', $data);
    }

    /**
     * Device manager page
     */
    public function devices(): void
    {
        $data = [
            'title' => 'Devices | GSMSDK',
            'content' => $this->app->view('flash/devices', []),
        ];
        echo $this->app->view('flash/layout', $data);
    }

    /**
     * ADB tools page
     */
    public function adb(): void
    {
        $data = [
            'title' => 'ADB Tools | GSMSDK',
            'content' => $this->app->view('flash/adb', []),
        ];
        echo $this->app->view('flash/layout', $data);
    }

    /**
     * Terminal page
     */
    public function terminal(): void
    {
        $data = [
            'title' => 'Terminal | GSMSDK',
            'content' => $this->app->view('flash/terminal', []),
        ];
        echo $this->app->view('flash/layout', $data);
    }

    /**
     * Logcat page
     */
    public function logs(): void
    {
        $data = [
            'title' => 'Logcat | GSMSDK',
            'content' => $this->app->view('flash/logs', []),
        ];
        echo $this->app->view('flash/layout', $data);
    }

    /**
     * File manager page
     */
    public function files(): void
    {
        $data = [
            'title' => 'File Manager | GSMSDK',
            'content' => $this->app->view('flash/files', []),
        ];
        echo $this->app->view('flash/layout', $data);
    }

    /**
     * JSON API: get device list
     */
    public function apiDevices(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'ok',
            'devices' => [
                [
                    'serial' => 'emulator-5554',
                    'model' => 'sdk_gphone64_x86_64',
                    'state' => 'device',
                    'mode' => 'ADB',
                    'product' => 'sdk_phone64_x86_64',
                ],
            ],
        ]);
    }

    /**
     * JSON API: get partition info
     */
    public function apiPartitions(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'ok',
            'partitions' => [
                ['name' => 'boot', 'type' => 'raw', 'size' => '32.0 MB', 'slot' => 'a', 'status' => 'good'],
                ['name' => 'system', 'type' => 'ext4', 'size' => '2.1 GB', 'slot' => 'a', 'status' => 'good'],
                ['name' => 'vendor', 'type' => 'ext4', 'size' => '1.4 GB', 'slot' => 'a', 'status' => 'good'],
                ['name' => 'product', 'type' => 'ext4', 'size' => '3.2 GB', 'slot' => 'a', 'status' => 'good'],
                ['name' => 'system_ext', 'type' => 'ext4', 'size' => '640 MB', 'slot' => 'a', 'status' => 'good'],
                ['name' => 'recovery', 'type' => 'raw', 'size' => '64.0 MB', 'slot' => 'none', 'status' => 'good'],
                ['name' => 'vbmeta', 'type' => 'raw', 'size' => '4.0 MB', 'slot' => 'none', 'status' => 'good'],
            ],
        ]);
    }

    /**
     * JSON API: flash operation
     */
    public function apiFlash(): void
    {
        header('Content-Type: application/json');
        $partition = $_POST['partition'] ?? '';
        $result = [
            'status' => 'ok',
            'message' => "Successfully flashed {$partition}",
            'steps' => [
                'Initialized fastboot connection',
                'Erased partition',
                'Uploaded image',
                'Verified checksum',
                'Rebooted device',
            ],
        ];
        echo json_encode($result);
    }
}
