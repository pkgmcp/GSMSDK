<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use GSMSDK\Core\Application;
use GSMSDK\ADB\ADBDevice;
use GSMSDK\Fastboot\FastbootDevice;
use GSMSDK\WebUSB\UsbManager;
use GSMSDK\WebUSB\TransportLayer;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

class DeviceController {
    protected Application $app;
    private static ?UsbManager $usbManager = null;
    private static ?array $connectedDevices = [];
    
    public function __construct(Application $app) {
        $this->app = $app;
        if (self::$usbManager === null) {
            self::$usbManager = new UsbManager();
        }
    }
    
    /**
     * List all connected devices
     */
    public function index(Request $request): Response {
        $devices = $this->scanDevices();
        
        return Response::json(['devices' => $devices]);
    }
    
    /**
     * Get device details
     */
    public function show(Request $request, string $id): Response {
        $device = $this->getDevice($id);
        
        if (!$device) {
            return Response::json(['error' => 'Device not found'], 404);
        }
        
        return Response::json(['device' => $device]);
    }
    
    /**
     * Connect to device via WebUSB
     */
    public function connect(Request $request): Response {
        $data = $request->all();
        
        if (!isset($data['vendorId'], $data['productId'], $data['serialNumber'])) {
            return Response::json(['error' => 'Missing required parameters'], 400);
        }
        
        try {
            $connected = self::$usbManager->connect(
                $data['vendorId'],
                $data['productId'],
                $data['serialNumber']
            );
            
            if ($connected) {
                $deviceInfo = self::$usbManager->getDeviceInfo();
                $deviceId = $data['serialNumber'];
                
                self::$connectedDevices[$deviceId] = array_merge($deviceInfo, [
                    'connected' => true,
                    'connectedAt' => date('Y-m-d H:i:s')
                ]);
                
                return Response::json([
                    'success' => true,
                    'message' => 'Device connected successfully',
                    'device' => self::$connectedDevices[$deviceId]
                ]);
            }
            
            return Response::json(['error' => 'Failed to connect to device'], 500);
            
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Disconnect from device
     */
    public function disconnect(Request $request, string $id): Response {
        if (isset(self::$connectedDevices[$id])) {
            self::$usbManager->disconnect();
            unset(self::$connectedDevices[$id]);
            
            return Response::json([
                'success' => true,
                'message' => 'Device disconnected'
            ]);
        }
        
        return Response::json(['error' => 'Device not found'], 404);
    }
    
    /**
     * Execute ADB command
     */
    public function shell(Request $request, string $id): Response {
        $command = $request->post('command');
        
        if (empty($command)) {
            return Response::json(['error' => 'Command required'], 400);
        }
        
        try {
            $adb = new ADBDevice($id);
            $result = $adb->shell($command);
            
            return Response::json([
                'output' => $result['output'],
                'exit_code' => $result['exit_code']
            ]);
            
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Reboot device
     */
    public function reboot(Request $request, string $id): Response {
        $mode = $request->post('mode', 'normal');
        
        try {
            $adb = new ADBDevice($id);
            $success = $adb->reboot($mode);
            
            return Response::json([
                'success' => $success,
                'mode' => $mode
            ]);
            
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Flash partition (Fastboot)
     */
    public function flash(Request $request, string $id): Response {
        $data = $request->all();
        
        if (!isset($data['partition'], $data['image'])) {
            return Response::json(['error' => 'Partition and image required'], 400);
        }
        
        try {
            $fastboot = new FastbootDevice();
            $result = $fastboot->flash(
                $data['partition'],
                $data['image'],
                $data['disableVerification'] ?? false
            );
            
            return Response::json($result);
            
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get device partitions
     */
    public function partitions(Request $request, string $id): Response {
        try {
            $fastboot = new FastbootDevice();
            if (!$fastboot->connect()) {
                return Response::json(['error' => 'No fastboot device'], 404);
            }
            
            $partitions = [
                'boot', 'system', 'vendor', 'product', 
                'system_ext', 'recovery', 'vbmeta'
            ];
            
            $result = [];
            foreach ($partitions as $partition) {
                $result[] = [
                    'name' => $partition,
                    'size' => $fastboot->getVariable($partition . '-size') ?: 'unknown',
                    'type' => 'raw'
                ];
            }
            
            return Response::json(['partitions' => $result]);
            
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Initialize Samsung Download Mode
     */
    public function samsungDownload(Request $request): Response {
        try {
            $samsung = new \GSMSDK\Samsung\DownloadMode();
            
            $deviceId = $request->post('deviceId');
            if ($deviceId) {
                $samsung->connectADB($deviceId);
                if ($samsung->rebootToDownloadMode()) {
                    return Response::json([
                        'success' => true,
                        'message' => 'Rebooting to Download Mode'
                    ]);
                }
            }
            
            return Response::json(['error' => 'Failed to enter Download Mode'], 500);
            
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Flash Samsung firmware
     */
    public function flashSamsung(Request $request): Response {
        $data = $request->all();
        
        if (!isset($data['tarMd5Path'])) {
            return Response::json(['error' => 'Firmware path required'], 400);
        }
        
        try {
            $samsung = new \GSMSDK\Samsung\DownloadMode();
            $result = $samsung->flashTarMD5($data['tarMd5Path'], $data['options'] ?? []);
            
            return Response::json($result);
            
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Scan for connected devices
     */
    private function scanDevices(): array {
        $devices = [];
        
        // Scan ADB devices
        exec('adb devices 2>&1', $adbOutput, $adbExitCode);
        if ($adbExitCode === 0) {
            foreach ($adbOutput as $line) {
                if (preg_match('/^(\S+)\s+(\S+)$/', $line, $matches)) {
                    if ($matches[1] !== 'List') {
                        $devices[] = [
                            'id' => $matches[1],
                            'state' => $matches[2],
                            'type' => 'adb',
                            'model' => $this->getDeviceModel($matches[1]),
                            'manufacturer' => 'Unknown',
                            'mode' => 'adb',
                            'authorized' => $matches[2] === 'device',
                            'online' => $matches[2] === 'device',
                            'knoxStatus' => 'unknown',
                            'battery' => null
                        ];
                    }
                }
            }
        }
        
        // Scan Fastboot devices
        exec('fastboot devices 2>&1', $fastbootOutput, $fastbootExitCode);
        if ($fastbootExitCode === 0) {
            foreach ($fastbootOutput as $line) {
                if (preg_match('/^(\S+)\s+(\S+)$/', $line, $matches)) {
                    $devices[] = [
                        'id' => $matches[1],
                        'state' => $matches[2],
                        'type' => 'fastboot',
                        'model' => 'Unknown',
                        'manufacturer' => 'Unknown',
                        'mode' => 'fastboot',
                        'authorized' => false,
                        'online' => true,
                        'knoxStatus' => 'unknown',
                        'battery' => null
                    ];
                }
            }
        }
        
        // Add connected USB devices
        foreach (self::$connectedDevices as $id => $device) {
            $devices[] = array_merge([
                'id' => $id,
                'type' => 'usb'
            ], $device);
        }
        
        return $devices;
    }
    
    /**
     * Get device model
     */
    private function getDeviceModel(string $serial): string {
        $adb = new ADBDevice($serial);
        $props = $adb->getProperties();
        return $props['ro.product.model'] ?? $serial;
    }
    
    /**
     * Get specific device
     */
    private function getDevice(string $id): ?array {
        // Check connected devices first
        if (isset(self::$connectedDevices[$id])) {
            return self::$connectedDevices[$id];
        }
        
        // Scan for device
        $devices = $this->scanDevices();
        foreach ($devices as $device) {
            if ($device['id'] === $id) {
                return $device;
            }
        }
        
        return null;
    }
    
    /**
     * Get device status
     */
    public function status(Request $request, string $id): Response {
        $device = $this->getDevice($id);
        
        if (!$device) {
            return Response::json(['error' => 'Device not found'], 404);
        }
        
        return Response::json(['status' => $device['state'] ?? 'unknown']);
    }
    
    /**
     * Get USB manager status
     */
    public function usbStatus(Request $request): Response {
        return Response::json([
            'connected' => !empty(self::$connectedDevices),
            'devices' => self::$connectedDevices,
            'mode' => self::$usbManager->getMode(),
            'vendorId' => self::$usbManager->getVendorId(),
            'serialNumber' => self::$usbManager->getSerialNumber()
        ]);
    }
}
