<?php

declare(strict_types=1);

namespace GSMSDK\Fastboot;

use GSMSDK\Contracts\ContainerInterface;
use GSMSDK\Traits\Configurable;
use RuntimeException;

/**
 * Fastboot Device Manager
 *
 * Integrates fastboot-php library for fastboot operations
 * within the GSMSDK framework.
 *
 * Note: Requires pkgmcp/fastboot-php as dependency
 */
class FastbootDevice
{
    use Configurable;

    /** @var bool Whether device is connected */
    private bool $connected = false;

    /** @var string|null Current device serial */
    private ?string $serial = null;

    /** @var array<string, mixed> Device variables cache */
    private array $variables = [];

    public function __construct(
        private ?ContainerInterface $container = null,
        array $config = []
    ) {
        $this->config = array_merge([
            'transport' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 5556,
            'timeout' => 30,
        ], $config);
    }

    /**
     * Check if fastboot-php library is available
     */
    public function isLibraryAvailable(): bool
    {
        return class_exists('FastbootPhp\FastbootDevice');
    }

    /**
     * Connect to device in fastboot mode
     *
     * @throws RuntimeException If library not available or connection fails
     */
    public function connect(?string $serial = null): bool
    {
        if (!$this->isLibraryAvailable()) {
            throw new RuntimeException(
                'Fastboot library not available. Install pkgmcp/fastboot-php'
            );
        }

        try {
            $transport = new \FastbootPhp\Transport\TcpTransport(
                $this->config['host'],
                $this->config['port']
            );

            $device = new \FastbootPhp\FastbootDevice($transport);
            $device->connect();

            $this->connected = true;
            $this->serial = $serial;
            $this->fetchVariables($device);

            return true;
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Fastboot connection failed: ' . $e->getMessage(),
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
        $this->variables = [];
        return true;
    }

    /**
     * Check if connected to device
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Get device serial number
     */
    public function getSerial(): ?string
    {
        return $this->serial;
    }

    /**
     * Fetch and cache device variables
     */
    private function fetchVariables(\FastbootPhp\FastbootDevice $device): void
    {
        $variables = [
            'product', 'version-bootloader', 'serialno',
            'secure', 'unlocked', 'off-mode-charge',
            'battery-voltage', 'partition-type:boot',
            'partition-size:boot',
        ];

        foreach ($variables as $var) {
            try {
                $this->variables[$var] = $device->getVariable($var);
            } catch (\Throwable $e) {
                $this->variables[$var] = null;
            }
        }
    }

    /**
     * Get device variable
     *
     * @param  string  $name  Variable name
     * @return string|null  Variable value
     */
    public function getVariable(string $name): ?string
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * Get all cached variables
     *
     * @return array<string, string|null>
     */
    public function getAllVariables(): array
    {
        return $this->variables;
    }

    /**
     * Flash partition with image
     *
     * @param  string  $partition  Partition name
     * @param  string  $imagePath  Path to image file
     * @throws RuntimeException If flash fails
     */
    public function flash(string $partition, string $imagePath): bool
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to fastboot device');
        }

        if (!file_exists($imagePath)) {
            throw new RuntimeException("Image not found: {$imagePath}");
        }

        // Delegate to fastboot-php library
        if ($this->isLibraryAvailable()) {
            try {
                $transport = new \FastbootPhp\Transport\TcpTransport(
                    $this->config['host'],
                    $this->config['port']
                );
                $device = new \FastbootPhp\FastbootDevice($transport);
                $device->connect();
                $device->flashBlob($partition, file_get_contents($imagePath));
                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Flash failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('Fastboot library not available');
    }

    /**
     * Erase partition
     *
     * @param  string  $partition  Partition name
     * @throws RuntimeException If erase fails
     */
    public function erase(string $partition): bool
    {
        if (!$this->connected) {
            throw new RuntimeException('Not connected to fastboot device');
        }

        if ($this->isLibraryAvailable()) {
            try {
                $transport = new \FastbootPhp\Transport\TcpTransport(
                    $this->config['host'],
                    $this->config['port']
                );
                $device = new \FastbootPhp\FastbootDevice($transport);
                $device->connect();
                $device->erase($partition);
                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Erase failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('Fastboot library not available');
    }

    /**
     * Reboot device
     *
     * @param  string  $mode  Reboot mode (bootloader|recovery|)
     */
    public function reboot(string $mode = ''): bool
    {
        if ($this->isLibraryAvailable()) {
            try {
                $transport = new \FastbootPhp\Transport\TcpTransport(
                    $this->config['host'],
                    $this->config['port']
                );
                $device = new \FastbootPhp\FastbootDevice($transport);
                $device->connect();

                match ($mode) {
                    'bootloader' => $device->rebootBootloader(),
                    'recovery' => $device->rebootRecovery(),
                    default => $device->reboot(),
                };

                $this->disconnect();
                return true;
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    'Reboot failed: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new RuntimeException('Fastboot library not available');
    }

    /**
     * Lock bootloader
     */
    public function lock(): bool
    {
        return $this->executeFastbootCommand('lock');
    }

    /**
     * Unlock bootloader
     */
    public function unlock(): bool
    {
        return $this->executeFastbootCommand('unlock');
    }

    /**
     * Execute fastboot command via library
     */
    private function executeFastbootCommand(string $command): bool
    {
        if (!$this->isLibraryAvailable()) {
            throw new RuntimeException('Fastboot library not available');
        }

        try {
            $transport = new \FastbootPhp\Transport\TcpTransport(
                $this->config['host'],
                $this->config['port']
            );
            $device = new \FastbootPhp\FastbootDevice($transport);
            $device->connect();

            match ($command) {
                'lock' => $device->lock(),
                'unlock' => $device->unlock(),
                default => throw new RuntimeException("Unknown command: {$command}"),
            };

            return true;
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Command failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Update firmware from ZIP
     *
     * @param  string  $zipPath  Path to update.zip
     * @param  bool    $wipe     Whether to wipe data
     * @throws RuntimeException If library not available
     */
    public function update(string $zipPath, bool $wipe = false): bool
    {
        throw new RuntimeException(
            'Firmware update not yet implemented in fastboot-php library'
        );
    }
}
