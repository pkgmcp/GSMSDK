<?php

declare(strict_types=1);

namespace GSMSDK;

use GSMSDK\ADB\ADBDevice;
use GSMSDK\Fastboot\FastbootDevice;
use GSMSDK\Traits\Configurable;

/**
 * Unified Device Manager
 *
 * Provides unified interface for ADB and Fastboot device operations.
 * Supports both Android device management and firmware flashing.
 */
class DeviceManager
{
    use Configurable;

    /** @var ADBDevice|null ADB device instance */
    private ?ADBDevice $adbDevice = null;

    /** @var FastbootDevice|null Fastboot device instance */
    private ?FastbootDevice $fastbootDevice = null;

    /** @var string Current mode: 'adb' or 'fastboot' */
    private string $mode = 'none';

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'adb' => [
                'host' => '127.0.0.1',
                'port' => 5037,
            ],
            'fastboot' => [
                'host' => '127.0.0.1',
                'port' => 5556,
            ],
        ], $config);
    }

    /**
     * Connect to device via ADB
     */
    public function connectADB(string $serial): ADBDevice
    {
        $this->adbDevice = new ADBDevice(null, $this->config['adb']);
        $this->adbDevice->connect($serial);
        $this->mode = 'adb';
        return $this->adbDevice;
    }

    /**
     * Connect to device via Fastboot
     */
    public function connectFastboot(string $serial): FastbootDevice
    {
        $this->fastbootDevice = new FastbootDevice(null, $this->config['fastboot']);
        $this->fastbootDevice->connect($serial);
        $this->mode = 'fastboot';
        return $this->fastbootDevice;
    }

    /**
     * Get current ADB device
     */
    public function getADBDevice(): ?ADBDevice
    {
        return $this->adbDevice;
    }

    /**
     * Get current Fastboot device
     */
    public function getFastbootDevice(): ?FastbootDevice
    {
        return $this->fastbootDevice;
    }

    /**
     * Get current mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Check if ADB device is connected
     */
    public function hasADB(): bool
    {
        return $this->adbDevice !== null && $this->adbDevice->isConnected();
    }

    /**
     * Check if Fastboot device is connected
     */
    public function hasFastboot(): bool
    {
        return $this->fastbootDevice !== null && $this->fastbootDevice->isConnected();
    }

    /**
     * Disconnect all devices
     */
    public function disconnectAll(): void
    {
        if ($this->adbDevice) {
            $this->adbDevice->disconnect();
            $this->adbDevice = null;
        }

        if ($this->fastbootDevice) {
            $this->fastbootDevice->disconnect();
            $this->fastbootDevice = null;
        }

        $this->mode = 'none';
    }

    /**
     * Switch device from ADB to Fastboot mode
     */
    public function switchToFastboot(): bool
    {
        if (!$this->adbDevice) {
            return false;
        }

        // Reboot to bootloader
        $this->adbDevice->reboot('bootloader');

        // Disconnect ADB
        $serial = $this->adbDevice->getSerial();
        $this->adbDevice->disconnect();

        // Connect via Fastboot
        if ($serial) {
            $this->connectFastboot($serial);
            return true;
        }

        return false;
    }

    /**
     * Switch device from Fastboot to ADB mode
     */
    public function switchToADB(): bool
    {
        if (!$this->fastbootDevice) {
            return false;
        }

        // Reboot to system
        $this->fastbootDevice->reboot();

        // Disconnect Fastboot
        $serial = $this->fastbootDevice->getSerial();
        $this->fastbootDevice->disconnect();

        // Wait for device to boot
        sleep(5);

        // Connect via ADB
        if ($serial) {
            $this->connectADB($serial);
            return true;
        }

        return false;
    }
}
