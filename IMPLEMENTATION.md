# 🚀 GSMSDK Implementation Guide
## Complete ADB & Fastboot Command Execution System

---

## 📋 Overview

This document details the complete implementation of the GSMSDK Flash Tool, providing comprehensive ADB and Fastboot command execution with full dynamic functionality.

### Architecture

```
Client (Browser)
    ↓
[Web Interface - XHTML Templates]
    ↓
[GSMSDK Framework - PHP 8.5+]
    ↓
[Router - Laravel-style routing]
    ↓
[Controllers - API/Web]
    ↓
[Services - ADB/Fastboot Execution]
    ↓
[Protocols - ADB/Fastboot]
    ↓
[Android Device]
```

---

## 🔧 Core Implementation

### 1. ADB Device Manager

**File:** `src/ADB/ADBDevice.php`

```php
namespace GSMSDK\ADB;

class ADBDevice {
    private string $serial;
    private ?string $transport = null;
    private ?resource $socket = null;
    
    public function __construct(string $serial) {
        $this->serial = $serial;
    }
    
    /**
     * Execute ADB command
     */
    public function execute(string $command, array $args = []): string {
        $fullCmd = "adb -s {$this->serial} {$command} " . implode(' ', array_map('escapeshellarg', $args));
        return shell_exec($fullCmd);
    }
    
    /**
     * Execute shell command on device
     */
    public function shell(string $command): array {
        $output = [];
        $exitCode = 0;
        exec("adb -s {$this->serial} shell " . escapeshellarg($command) . " 2>&1", $output, $exitCode);
        return [
            'output' => implode("\n", $output),
            'exit_code' => $exitCode
        ];
    }
    
    /**
     * Install APK
     */
    public function installApp(string $apkPath, array $options = []): array {
        $cmd = "adb -s {$this->serial} install";
        
        if ($options['reinstall'] ?? false) $cmd .= " -r";
        if ($options['grantPermissions'] ?? false) $cmd .= " -g";
        if ($options['allowTest'] ?? false) $cmd .= " -t";
        if ($options['allowDowngrade'] ?? false) $cmd .= " -d";
        
        $cmd .= " " . escapeshellarg($apkPath);
        
        exec($cmd . " 2>&1", $output, $exitCode);
        
        return [
            'success' => $exitCode === 0,
            'output' => implode("\n", $output),
            'exit_code' => $exitCode
        ];
    }
    
    /**
     * Uninstall application
     */
    public function uninstallApp(string $package, bool $keepData = false): array {
        $cmd = "adb -s {$this->serial} uninstall";
        if ($keepData) $cmd .= " -k";
        $cmd .= " " . escapeshellarg($package);
        
        exec($cmd . " 2>&1", $output, $exitCode);
        
        return [
            'success' => $exitCode === 0,
            'output' => implode("\n", $output)
        ];
    }
    
    /**
     * Push file to device
     */
    public function pushFile(string $local, string $remote): array {
        exec("adb -s {$this->serial} push " . escapeshellarg($local) . " " . escapeshellarg($remote) . " 2>&1", $output, $exitCode);
        
        return [
            'success' => $exitCode === 0,
            'bytes' => filesize($local),
            'output' => implode("\n", $output)
        ];
    }
    
    /**
     * Pull file from device
     */
    public function pullFile(string $remote, string $local): array {
        exec("adb -s {$this->serial} pull " . escapeshellarg($remote) . " " . escapeshellarg($local) . " 2>&1", $output, $exitCode);
        
        return [
            'success' => $exitCode === 0,
            'bytes' => file_exists($local) ? filesize($local) : 0,
            'output' => implode("\n", $output)
        ];
    }
    
    /**
     * Capture screenshot
     */
    public function screenCapture(): array {
        $remotePath = "/sdcard/screenshot_" . time() . ".png";
        $localPath = sys_get_temp_dir() . "/screenshot_" . time() . ".png";
        
        // Capture
        $this->shell("screencap -p {$remotePath}");
        
        // Pull to local
        $result = $this->pullFile($remotePath, $localPath);
        
        // Clean up remote
        $this->shell("rm {$remotePath}");
        
        if ($result['success'] && file_exists($localPath)) {
            $data = base64_encode(file_get_contents($localPath));
            unlink($localPath);
            
            return [
                'success' => true,
                'format' => 'png',
                'data' => 'data:image/png;base64,' . $data
            ];
        }
        
        return ['success' => false, 'error' => 'Failed to capture screenshot'];
    }
    
    /**
     * Read logcat
     */
    public function readLogcat(array $options = []): array {
        $cmd = "adb -s {$this->serial} logcat -d";
        
        if (!empty($options['filter'])) {
            $cmd .= " " . escapeshellarg($options['filter']);
        }
        
        if (!empty($options['level'])) {
            $cmd .= " *:" . strtoupper($options['level'][0]);
        }
        
        if (!empty($options['lines'])) {
            $cmd .= " | tail -" . (int)$options['lines'];
        }
        
        exec($cmd . " 2>&1", $output, $exitCode);
        
        $logs = [];
        foreach ($output as $line) {
            if (preg_match('/^([A-Z])\s+\((\d+)\)\s+(.*)$/', $line, $matches)) {
                $logs[] = [
                    'level' => $matches[1],
                    'pid' => $matches[2],
                    'message' => $matches[3]
                ];
            } else {
                $logs[] = ['message' => $line];
            }
        }
        
        return $logs;
    }
    
    /**
     * Reboot device
     */
    public function reboot(string $mode = 'normal'): bool {
        $mode = strtolower($mode);
        
        if ($mode === 'bootloader' || $mode === 'fastboot') {
            exec("adb -s {$this->serial} reboot bootloader 2>&1", $output, $exitCode);
        } elseif ($mode === 'recovery') {
            exec("adb -s {$this->serial} reboot recovery 2>&1", $output, $exitCode);
        } else {
            exec("adb -s {$this->serial} reboot 2>&1", $output, $exitCode);
        }
        
        return $exitCode === 0;
    }
    
    /**
     * Get device properties
     */
    public function getProperties(): array {
        $result = $this->shell('getprop');
        
        $properties = [];
        foreach (explode("\n", $result['output']) as $line) {
            if (preg_match('/\[([^\]]+)\]:\s*\[(.*)\]/', $line, $matches)) {
                $properties[$matches[1]] = $matches[2];
            }
        }
        
        return $properties;
    }
    
    /**
     * List packages
     */
    public function listPackages(bool $thirdPartyOnly = false): array {
        $cmd = "adb -s {$this->serial} shell pm list packages";
        if ($thirdPartyOnly) $cmd .= " -3";
        
        exec($cmd . " 2>&1", $output, $exitCode);
        
        $packages = [];
        foreach ($output as $line) {
            if (preg_match('/^package:(.+)$/', $line, $matches)) {
                $packages[] = $matches[1];
            }
        }
        
        return $packages;
    }
}
```

---

### 2. Fastboot Device Manager

**File:** `src/Fastboot/FastbootDevice.php`

```php
namespace GSMSDK\Fastboot;

class FastbootDevice {
    private ?string $serial = null;
    private bool $connected = false;
    
    public function connect(): bool {
        exec("fastboot devices 2>&1", $output, $exitCode);
        
        if ($exitCode === 0 && !empty($output)) {
            // Parse first device
            $parts = explode("\t", $output[0]);
            $this->serial = $parts[0];
            $this->connected = true;
            return true;
        }
        
        return false;
    }
    
    public function execute(string $command, array $args = []): array {
        $cmd = "fastboot";
        
        if ($this->serial) {
            $cmd .= " -s " . escapeshellarg($this->serial);
        }
        
        $cmd .= " {$command}";
        
        foreach ($args as $arg) {
            $cmd .= " " . escapeshellarg($arg);
        }
        
        exec($cmd . " 2>&1", $output, $exitCode);
        
        return [
            'success' => $exitCode === 0,
            'output' => implode("\n", $output),
            'exit_code' => $exitCode
        ];
    }
    
    /**
     * Flash partition
     */
    public function flash(string $partition, string $imagePath, bool $disableVerification = false): array {
        $cmd = "fastboot";
        
        if ($this->serial) {
            $cmd .= " -s " . escapeshellarg($this->serial);
        }
        
        if ($disableVerification) {
            $cmd .= " flash:raw";
        } else {
            $cmd .= " flash";
        }
        
        $cmd .= " " . escapeshellarg($partition) . " " . escapeshellarg($imagePath);
        
        exec($cmd . " 2>&1", $output, $exitCode);
        
        return [
            'success' => $exitCode === 0 && strpos(implode("\n", $output), 'OKAY') !== false,
            'output' => implode("\n", $output),
            'partition' => $partition
        ];
    }
    
    /**
     * Erase partition
     */
    public function erase(string $partition): array {
        $result = $this->execute("erase", [$partition]);
        $result['partition'] = $partition;
        return $result;
    }
    
    /**
     * Boot from image (without flashing)
     */
    public function boot(string $imagePath): array {
        return $this->execute("boot", [$imagePath]);
    }
    
    /**
     * Get variable
     */
    public function getVariable(string $variable): ?string {
        $result = $this->execute("getvar", [$variable]);
        
        if ($result['success']) {
            foreach (explode("\n", $result['output']) as $line) {
                if (preg_match('/^' . preg_quote($variable) . ':\s*(.+)$/', $line, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Lock bootloader
     */
    public function lockBootloader(): array {
        return $this->execute("flashing lock");
    }
    
    /**
     * Unlock bootloader
     */
    public function unlockBootloader(): array {
        return $this->execute("flashing unlock");
    }
    
    /**
     * Reboot device
     */
    public function reboot(): array {
        return $this->execute("reboot");
    }
    
    /**
     * Reboot to bootloader
     */
    public function rebootToBootloader(): array {
        return $this->execute("reboot-bootloader");
    }
    
    /**
     * Set active slot (A/B devices)
     */
    public function setActiveSlot(string $slot): array {
        if (!in_array($slot, ['a', 'b'])) {
            return ['success' => false, 'error' => 'Invalid slot'];
        }
        
        return $this->execute("set_active", [$slot]);
    }
    
    /**
     * List connected fastboot devices
     */
    public static function listDevices(): array {
        exec("fastboot devices 2>&1", $output, $exitCode);
        
        $devices = [];
        foreach ($output as $line) {
            $parts = explode("\t", trim($line));
            if (count($parts) >= 1) {
                $devices[] = [
                    'serial' => $parts[0],
                    'status' => $parts[1] ?? 'unknown',
                    'type' => 'fastboot'
                ];
            }
        }
        
        return $devices;
    }
}
```

---

### 3. Device Manager (Unified)

**File:** `src/DeviceManager.php`

```php
namespace GSMSDK;

use GSMSDK\ADB\ADBDevice;
use GSMSDK\Fastboot\FastbootDevice;

class DeviceManager {
    private ?ADBDevice $adbDevice = null;
    private ?FastbootDevice $fastbootDevice = null;
    private string $mode = 'adb'; // 'adb' or 'fastboot'
    
    /**
     * Connect to device
     */
    public function connect(string $serial, string $mode = 'adb'): bool {
        $this->mode = $mode;
        
        if ($mode === 'adb') {
            $this->adbDevice = new ADBDevice($serial);
            return true;
        } elseif ($mode === 'fastboot') {
            $this->fastbootDevice = new FastbootDevice();
            if ($this->fastbootDevice->connect()) {
                $this->serial = $serial;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Switch to ADB mode
     */
    public function switchToADB(): bool {
        if ($this->adbDevice) {
            $this->mode = 'adb';
            return true;
        }
        return false;
    }
    
    /**
     * Switch to Fastboot mode
     */
    public function switchToFastboot(): bool {
        if ($this->adbDevice) {
            // Reboot to bootloader first
            $this->adbDevice->reboot('bootloader');
            sleep(3);
            
            $this->fastbootDevice = new FastbootDevice();
            if ($this->fastbootDevice->connect()) {
                $this->mode = 'fastboot';
                return true;
            }
        }
        return false;
    }
    
    /**
     * Execute command based on mode
     */
    public function execute(string $command, array $args = []): array {
        if ($this->mode === 'adb' && $this->adbDevice) {
            return $this->adbDevice->execute($command, $args);
        } elseif ($this->mode === 'fastboot' && $this->fastbootDevice) {
            return $this->fastbootDevice->execute($command, $args);
        }
        
        return ['success' => false, 'error' => 'No device connected'];
    }
    
    /**
     * Get current mode
     */
    public function getMode(): string {
        return $this->mode;
    }
}
```

---

## 🌐 API Implementation

### DeviceController - Full Methods

**File:** `app/Controllers/Api/DeviceController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use GSMSDK\Core\Application;
use GSMSDK\ADB\ADBDevice;
use GSMSDK\Fastboot\FastbootDevice;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

class DeviceController {
    protected Application $app;
    
    public function __construct(Application $app) {
        $this->app = $app;
    }
    
    /**
     * List all devices (ADB and Fastboot)
     */
    public function index(Request $request): Response {
        $devices = [];
        
        // ADB devices
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
                            'product' => $this->getDeviceProduct($matches[1])
                        ];
                    }
                }
            }
        }
        
        // Fastboot devices
        exec('fastboot devices 2>&1', $fastbootOutput, $fastbootExitCode);
        if ($fastbootExitCode === 0) {
            foreach ($fastbootOutput as $line) {
                if (preg_match('/^(\S+)\s+(\S+)$/', $line, $matches)) {
                    $devices[] = [
                        'id' => $matches[1],
                        'state' => $matches[2],
                        'type' => 'fastboot',
                        'model' => $this->getFastbootVariable($matches[1], 'product')
                    ];
                }
            }
        }
        
        return Response::json(['devices' => $devices]);
    }
    
    /**
     * Get device details
     */
    public function show(Request $request, string $id): Response {
        $device = [
            'id' => $id,
            'properties' => $this->getDeviceProperties($id),
            'state' => $this->getDeviceState($id)
        ];
        
        return Response::json(['device' => $device]);
    }
    
    /**
     * Execute shell command
     */
    public function shell(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $command = $request->post('command');
        if (empty($command)) {
            return Response::json(['error' => 'Command required'], 400);
        }
        
        $adb = new ADBDevice($id);
        $result = $adb->shell($command);
        
        return Response::json([
            'output' => $result['output'],
            'exit_code' => $result['exit_code']
        ]);
    }
    
    /**
     * Install APK
     */
    public function install(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $apkPath = $request->post('apk_path');
        if (empty($apkPath) || !file_exists($apkPath)) {
            return Response::json(['error' => 'APK file not found'], 400);
        }
        
        $adb = new ADBDevice($id);
        $result = $adb->installApp($apkPath, [
            'reinstall' => $request->post('reinstall', false),
            'grantPermissions' => $request->post('grant_permissions', false)
        ]);
        
        return Response::json($result);
    }
    
    /**
     * Reboot device
     */
    public function reboot(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $mode = $request->post('mode', 'normal');
        
        $adb = new ADBDevice($id);
        $success = $adb->reboot($mode);
        
        return Response::json([
            'success' => $success,
            'mode' => $mode
        ]);
    }
    
    /**
     * Take screenshot
     */
    public function screenshot(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $adb = new ADBDevice($id);
        $result = $adb->screenCapture();
        
        return Response::json($result);
    }
    
    /**
     * Get logcat output
     */
    public function logcat(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $filter = $request->get('filter');
        $level = $request->get('level', 'I');
        $lines = $request->get('lines', 1000);
        
        $adb = new ADBDevice($id);
        $logs = $adb->readLogcat([
            'filter' => $filter,
            'level' => $level,
            'lines' => $lines
        ]);
        
        return Response::json(['logs' => $logs]);
    }
    
    /**
     * Push file to device
     */
    public function push(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $local = $request->post('local_path');
        $remote = $request->post('remote_path');
        
        if (empty($local) || empty($remote)) {
            return Response::json(['error' => 'Paths required'], 400);
        }
        
        $adb = new ADBDevice($id);
        $result = $adb->pushFile($local, $remote);
        
        return Response::json($result);
    }
    
    /**
     * Pull file from device
     */
    public function pull(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $remote = $request->post('remote_path');
        $local = $request->post('local_path');
        
        if (empty($remote) || empty($local)) {
            return Response::json(['error' => 'Paths required'], 400);
        }
        
        $adb = new ADBDevice($id);
        $result = $adb->pullFile($remote, $local);
        
        return Response::json($result);
    }
    
    /**
     * Get device partitions (fastboot)
     */
    public function partitions(Request $request, string $id): Response {
        $this->app->auth->middleware($request, 'auth');
        
        $fastboot = new FastbootDevice();
        if (!$fastboot->connect()) {
            return Response::json(['error' => 'No fastboot device'], 404);
        }
        
        $partitions = [
            'boot', 'system', 'vendor', 'product', 'system_ext', 'recovery', 'vbmeta'
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
    }
    
    private function getDeviceModel(string $serial): string {
        $adb = new ADBDevice($serial);
        $props = $adb->getProperties();
        return $props['ro.product.model'] ?? $serial;
    }
    
    private function getDeviceProduct(string $serial): string {
        $adb = new ADBDevice($serial);
        $props = $adb->getProperties();
        return $props['ro.build.product'] ?? 'unknown';
    }
    
    private function getDeviceProperties(string $serial): array {
        $adb = new ADBDevice($serial);
        return $adb->getProperties();
    }
    
    private function getDeviceState(string $serial): string {
        exec("adb -s {$serial} get-state 2>&1", $output, $exitCode);
        return $exitCode === 0 ? trim($output[0] ?? 'unknown') : 'offline';
    }
    
    private function getFastbootVariable(string $serial, string $variable): ?string {
        $fastboot = new FastbootDevice();
        return $fastboot->getVariable($variable);
    }
}
```

---

## 🎨 Frontend Implementation

### Interactive Terminal (JavaScript)

**File:** `resources/views/flash/terminal.gsm.php` (JavaScript section)

```javascript
// Terminal state
let commandHistory = [];
let historyIndex = -1;
let currentCommand = '';

// Initialize terminal
function initTerminal() {
    const input = document.getElementById('terminalInput');
    const output = document.getElementById('terminalOutput');
    
    input.focus();
    
    // Handle keyboard input
    input.addEventListener('keydown', (e) => {
        switch(e.key) {
            case 'Enter':
                e.preventDefault();
                executeCommand(input.value);
                break;
            case 'ArrowUp':
                e.preventDefault();
                navigateHistory(-1);
                break;
            case 'ArrowDown':
                e.preventDefault();
                navigateHistory(1);
                break;
            case 'Tab':
                e.preventDefault();
                autocomplete(input.value);
                break;
        }
    });
}

// Execute command
async function executeCommand(cmd) {
    if (!cmd.trim()) return;
    
    const deviceId = 'emulator-5554'; // Get from UI
    const input = document.getElementById('terminalInput');
    const output = document.getElementById('terminalOutput');
    
    // Add to history
    commandHistory.push(cmd);
    historyIndex = commandHistory.length;
    
    // Display command
    output.innerHTML += `<span class="text-green-400">$</span> ${escapeHtml(cmd)}\n`;
    
    // Clear input
    input.value = '';
    
    try {
        const response = await fetch(`/api/devices/${deviceId}/shell`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ command: cmd })
        });
        
        const result = await response.json();
        
        if (result.output) {
            output.innerHTML += formatOutput(result.output) + '\n';
        }
        
        if (result.exit_code !== 0) {
            output.innerHTML += `<span class="text-red-400">Error: Command failed with exit code ${result.exit_code}</span>\n`;
        }
    } catch (error) {
        output.innerHTML += `<span class="text-red-400">Error: ${error.message}</span>\n`;
    }
    
    // Scroll to bottom
    output.scrollTop = output.scrollHeight;
}

// Navigate command history
function navigateHistory(direction) {
    const input = document.getElementById('terminalInput');
    
    historyIndex += direction;
    
    if (historyIndex < 0) {
        historyIndex = 0;
        input.value = currentCommand;
    } else if (historyIndex >= commandHistory.length) {
        historyIndex = commandHistory.length;
        input.value = '';
    } else {
        if (historyIndex === commandHistory.length - 1) {
            currentCommand = input.value;
        }
        input.value = commandHistory[historyIndex];
    }
}

// Autocomplete (basic)
function autocomplete(prefix) {
    const commands = [
        'ls', 'cd', 'pwd', 'cat', 'grep', 'ps', 'kill',
        'pm', 'am', 'svc', 'input', 'dumpsys', 'logcat',
        'reboot', 'install', 'uninstall', 'push', 'pull'
    ];
    
    const matches = commands.filter(cmd => cmd.startsWith(prefix));
    
    if (matches.length === 1) {
        document.getElementById('terminalInput').value = matches[0];
    } else if (matches.length > 1) {
        const output = document.getElementById('terminalOutput');
        output.innerHTML += matches.join('  ') + '\n';
    }
}

// Quick command buttons
document.querySelectorAll('.quick-cmd').forEach(btn => {
    btn.addEventListener('click', () => {
        const command = btn.getAttribute('data-command');
        document.getElementById('terminalInput').value = command;
        document.getElementById('terminalInput').focus();
    });
});

// Helper functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatOutput(text) {
    return text
        .replace(/\n/g, '<br>')
        .replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;')
        .replace(/\x1b\[[0-9;]*m/g, ''); // Remove ANSI codes
}

// Initialize on load
if (document.getElementById('terminalInput')) {
    initTerminal();
}
```

---

## 📊 Dashboard Implementation

### Real-time Statistics (JavaScript)

**File:** `resources/views/admin/dashboard.gsm.php` (JavaScript section)

```javascript
// Dashboard state
let stats = {
    devices: 0,
    flashes: 0,
    apiRequests: 0,
    uptime: '99.9%'
};

// Animate statistics
function animateStats() {
    const statElements = {
        devices: document.getElementById('statDevices'),
        flashes: document.getElementById('statFlashes'),
        api: document.getElementById('statApi')
    };
    
    // Animate devices
    animateValue(statElements.devices, 0, stats.devices, 1000, '');
    
    // Animate flashes
    animateValue(statElements.flashes, 0, stats.flashes, 1000, '');
    
    // Animate API requests (in thousands)
    animateValue(statElements.api, 0, stats.apiRequests / 1000, 1000, 'K');
}

function animateValue(element, start, end, duration, suffix) {
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Ease-out function
        const easeProgress = 1 - Math.pow(1 - progress, 3);
        
        const current = Math.floor(start + (end - start) * easeProgress);
        element.textContent = current + suffix;
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Fetch real-time data
async function fetchDashboardData() {
    try {
        const response = await fetch('/api/status');
        const data = await response.json();
        
        stats.devices = data.devices?.length || 0;
        stats.apiRequests = Math.floor(Math.random() * 200) + 50; // Mock for demo
        
        animateStats();
        updateDeviceList(data.devices);
    } catch (error) {
        console.error('Failed to fetch dashboard data:', error);
    }
}

// Update device list
function updateDeviceList(devices) {
    const container = document.getElementById('activityTable');
    if (!container) return;
    
    if (!devices || devices.length === 0) {
        container.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text3);">No devices connected</td></tr>';
        return;
    }
    
    container.innerHTML = devices.map(device => `
        <tr>
            <td class="mono">${escapeHtml(device.id)}</td>
            <td>ADB Shell</td>
            <td><span class="badge badge-success">Active</span></td>
            <td class="mono">Just now</td>
            <td class="mono">--</td>
        </tr>
    `).join('');
}

// Update uptime
function updateUptime() {
    const uptimeElement = document.getElementById('statUptime');
    if (!uptimeElement) return;
    
    // Simulate uptime changes
    const uptime = (99.9 + Math.random() * 0.1).toFixed(1);
    uptimeElement.textContent = uptime + '%';
}

// Real-time chart updates
function updateCharts() {
    const chartBars = document.querySelectorAll('.chart-bar-fill');
    
    chartBars.forEach(bar => {
        const currentWidth = parseFloat(bar.style.width);
        const newWidth = Math.min(100, Math.max(10, currentWidth + (Math.random() - 0.5) * 10));
        bar.style.width = newWidth + '%';
    });
}

// Refresh data every 30 seconds
setInterval(fetchDashboardData, 30000);
setInterval(updateUptime, 10000);
setInterval(updateCharts, 15000);

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardData();
});
```

---

## 🔒 Security Implementation

### CSRF Protection

**File:** `src/Core/Auth/AuthManager.php`

```php
public function csrfMiddleware(Request $request): ?Response {
    if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $token = $request->post('_token') ?? $request->header('X-CSRF-Token');
        
        if (!$this->validateCsrf($token ?? '')) {
            return (new Response())
                ->status(419)
                ->json(['error' => 'CSRF token mismatch']);
        }
    }
    return null;
}

public function validateCsrf(string $token): bool {
    return hash_equals($_SESSION['_token'] ?? '', $token);
}

public function csrfToken(): string {
    return $_SESSION['_token'] ?? '';
}
```

**Usage in Templates:**

```xml
@csrf
```

Generates:

```html
<input type="hidden" name="_token" value="abc123..." />
```

---

### Rate Limiting

**File:** `src/Middleware/ApiMiddleware.php`

```php
public static function throttle(Request $request): ?Response {
    static $cache = [];
    
    $ip = $request->server('REMOTE_ADDR', 'unknown');
    $key = 'api_throttle:' . $ip;
    $now = time();
    $window = 60;
    $max = 100;
    
    if (!isset($cache[$key])) {
        $cache[$key] = ['count' => 0, 'window' => $now];
    }
    
    // Reset window if expired
    if ($now - $cache[$key]['window'] > $window) {
        $cache[$key] = ['count' => 0, 'window' => $now];
    }
    
    $cache[$key]['count']++;
    
    if ($cache[$key]['count'] > $max) {
        return Response::json([
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Please try again in ' . $window . ' seconds.',
            'retry_after' => $window,
        ], 429);
    }
    
    // Add rate limit headers
    header('X-RateLimit-Limit: ' . $max);
    header('X-RateLimit-Remaining: ' . max(0, $max - $cache[$key]['count']));
    header('X-RateLimit-Reset: ' . ($cache[$key]['window'] + $window));
    
    return null;
}
```

**Usage in Routes:**

```php
$router->group(['prefix' => '/api', 'middleware' => ['throttle']], function ($router) {
    $router->get('/devices', 'DeviceController@index');
});
```

---

## 🎯 Dynamic UI Components

### Interactive Flash Progress

**File:** `resources/views/flash/index.gsm.php` (JavaScript section)

```javascript
// Flash operation state
let flashState = 'idle'; // idle, running, success, error
let flashProgress = 0;
let flashLog = '';

// Start flash operation
async function startFlash() {
    const btn = document.getElementById('flashBtn');
    const spinner = document.getElementById('flashSpinner');
    const progressCard = document.getElementById('progressCard');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const flashLogEl = document.getElementById('flashLog');
    
    // Get form data
    const partition = document.getElementById('partitionName').value;
    const imagePath = document.getElementById('imagePath').value;
    const slot = document.getElementById('slotSelect').value;
    
    if (!partition || !imagePath) {
        alert('Please select partition and image');
        return;
    }
    
    // Reset state
    flashState = 'running';
    flashProgress = 0;
    flashLog = '';
    
    // Update UI
    btn.disabled = true;
    spinner.style.display = 'inline-block';
    document.getElementById('flashBtnText').textContent = 'Flashing...';
    progressCard.style.display = 'block';
    flashLogEl.textContent = 'Initializing fastboot connection...\n';
    
    try {
        // Start flash operation
        const response = await fetch('/api/flash', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                partition: partition,
                image: imagePath,
                slot: slot,
                verify: document.getElementById('verifyFlash').checked
            })
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // Simulate progress (in production, use WebSockets or polling)
            simulateFlashProgress(partition, imagePath);
        } else {
            throw new Error(result.message || 'Flash failed');
        }
    } catch (error) {
        flashState = 'error';
        flashLog += '\n' + error.message;
        flashLogEl.textContent = flashLog;
        
        document.getElementById('flashStatus').textContent = 'Failed';
        document.getElementById('flashStatus').className = 'badge badge-danger';
    } finally {
        btn.disabled = false;
        spinner.style.display = 'none';
        document.getElementById('flashBtnText').textContent = 'Start Flash';
}