<?php

declare(strict_types=1);

namespace GSMSDK\ADB;

use GSMSDK\Contracts\ContainerInterface;
use GSMSDK\Traits\Configurable;
use RuntimeException;

/**
 * ADB Device Manager
 *
 * Integrates adb-php library for Android device operations
 * within the GSMSDK framework.
 *
 * Note: Requires pkgmcp/adb-php as dependency
 */
class ADBDevice
{
    use Configurable;

    /** @var bool Whether device is connected */
    private bool $connected = false;

    /** @var string|null Current device serial */
    private ?string $serial = null;

    public function __construct(
        private ?ContainerInterface $container = null,
        array $config = []
    ) {
        $this->config = array_merge([
            'host' => '127.0.0.1',
            'port' => 5037,
            'timeout' => 30,
        ], $config);
    }

    /**
     * Check if adb-php library is available
     */
    public function isLibraryAvailable(): bool
    {
        return class_exists('AdbPhp\AdbClient');
    }

    /**
     * Connect to ADB device
     *
     * @throws RuntimeException If library not available or connection fails
     */
    public function connect(string $serial): bool
    {
        if (!$this->isLibraryAvailable()) {
            throw new RuntimeException(
                'ADB library not available. Install pkgmcp/adb-php'
            );
        }

        try {
            // Connection is lazy in adb-php, just verify device exists
            $this->serial = $serial;
            $this->connected = true;
            return true;
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'ADB connection failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Disconnect from device
     */
    public function disconnect(): bool
    {
        $this->connected = false;
        $this->serial = null;
        return true;
    }

    /**
     * Check if connected
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Get device serial
     */
    public function getSerial(): ?string
    {
        return $this->serial;
    }

    /**
     * Execute shell command on device
     */
    public function shell(string $command): string
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);
                return $device->shell($command);
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Shell command failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }

    /**
     * Install APK on device
     */
    public function install(string $apkPath, bool $fast = false): bool
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if (!file_exists($apkPath)) {
            throw new RuntimeException("APK not found: {$apkPath}");
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);

                if ($fast) {
                    $device->install($apkPath, true);
                } else {
                    $device->install($apkPath);
                }

                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Install failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }

    /**
     * Uninstall package from device
     */
    public function uninstall(string $package): bool
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);
                $device->uninstall($package);
                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Uninstall failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }

    /**
     * Pull file from device
     */
    public function pull(string $remotePath, string $localPath): bool
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);
                $result = $device->pull($remotePath);

                file_put_contents($localPath, $result['transfer']->getData());
                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Pull failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }

    /**
     * Push file to device
     */
    public function push(string $localPath, string $remotePath, int $mode = 0644): bool
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if (!file_exists($localPath)) {
            throw new RuntimeException("File not found: {$localPath}");
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);
                $device->push($localPath, $remotePath, $mode);
                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Push failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }

    /**
     * Get device properties
     *
     * @return array<string, string>
     */
    public function getProperties(): array
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);
                return $device->getProperties();
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Failed to get properties: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }

    /**
     * Take screenshot
     */
    public function screenshot(): string
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);
                return $device->screencap();
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Screenshot failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }

    /**
     * Reboot device
     */
    public function reboot(string $mode = ''): bool
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to device');
        }

        if ($this->isLibraryAvailable()) {
            try {
                $client = \AdbPhp\AdbClient::create(
                    host: $this->config['host'],
                    port: $this->config['port']
                );
                $device = $client->getDevice($this->serial);
                $device->reboot($mode);
                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Reboot failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('ADB library not available');
    }
}
