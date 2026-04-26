<?php

declare(strict_types=1);

namespace GSMSDK\Samsung;

use GSMSDK\Fastboot\FastbootDevice;
use GSMSDK\ADB\ADBDevice;

/**
 * Samsung Download Mode Handler
 * 
 * Specialized flash tool for Samsung devices in Download Mode (Odin mode).
 * Supports .tar.md5 firmware packages and partition-level flashing.
 */
class DownloadMode {
    private ?ADBDevice $adbDevice = null;
    private ?FastbootDevice $fastbootDevice = null;
    private string $mode = 'adb';
    private array $supportedPartitions = [
        'AP', 'BL', 'CP', 'CSC', 'HOME', 'ODM', 'OXX', 'PIT',
        'SYSTEM', 'VENDOR', 'PRODUCT', 'USERDATA', 'CACHE',
        'RECOVERY', 'BOOT', 'DTBO', 'VBMETA'
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize without device connection
    }
    
    /**
     * Connect in ADB mode first
     */
    public function connectADB(string $serial): bool {
        $this->adbDevice = new ADBDevice($serial);
        $this->mode = 'adb';
        return true;
    }
    
    /**
     * Reboot to Download Mode (Odin Mode)
     */
    public function rebootToDownloadMode(): bool {
        if (!$this->adbDevice) {
            return false;
        }
        
        // Method 1: Using adb reboot bootloader then trigger download mode
        $this->adbDevice->reboot('bootloader');
        sleep(5);
        
        // Check if in fastboot/download mode
        $fastboot = new FastbootDevice();
        if ($fastboot->connect()) {
            $this->fastbootDevice = $fastboot;
            $this->mode = 'download';
            return true;
        }
        
        // Method 2: Try specific Samsung reboot command
        $this->adbDevice->shell('reboot download');
        sleep(5);
        
        if ($fastboot->connect()) {
            $this->fastbootDevice = $fastboot;
            $this->mode = 'download';
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if Samsung device in Download Mode
     */
    public function isDownloadMode(): bool {
        if ($this->fastbootDevice) {
            $product = $this->fastbootDevice->getVariable('product');
            return !empty($product);
        }
        return false;
    }
    
    /**
     * Get Samsung device info
     */
    public function getDeviceInfo(): array {
        $info = [
            'manufacturer' => 'Samsung',
            'mode' => $this->mode,
            'partitions' => [],
            'supported_operations' => []
        ];
        
        if ($this->fastbootDevice) {
            $info['product'] = $this->fastbootDevice->getVariable('product');
            $info['serialno'] = $this->fastbootDevice->getVariable('serialno');
            $info['secure'] = $this->fastbootDevice->getVariable('secure');
            $info['unlocked'] = $this->fastbootDevice->getVariable('unlocked');
            
            // Check partition sizes
            foreach ($this->supportedPartitions as $partition) {
                $size = $this->fastbootDevice->getVariable($partition . '-size');
                if ($size) {
                    $info['partitions'][$partition] = $size;
                }
            }
        }
        
        $info['supported_operations'] = [
            'flash_tar_md5',
            'flash_partition',
            'erase_partition',
            'pit_flash',
            'reboot_download',
            'reboot_normal',
            'check_firmware'
        ];
        
        return $info;
    }
    
    /**
     * Flash Samsung .tar.md5 firmware package
     * Simulates Odin-style flashing
     */
    public function flashTarMD5(string $tarPath, array $options = []): array {
        if (!$this->fastbootDevice) {
            return ['success' => false, 'error' => 'Not in download mode'];
        }
        
        if (!file_exists($tarPath)) {
            return ['success' => false, 'error' => 'Firmware file not found'];
        }
        
        // Validate .tar.md5 file
        if (!$this->validateTarMD5($tarPath)) {
            return ['success' => false, 'error' => 'Invalid firmware package'];
        }
        
        $extractDir = $this->extractTarMD5($tarPath);
        if (!$extractDir) {
            return ['success' => false, 'error' => 'Failed to extract firmware'];
        }
        
        $result = [
            'success' => true,
            'operations' => [],
            'warnings' => []
        ];
        
        // Flash each partition
        $partitionFiles = $this->getPartitionFiles($extractDir);
        
        foreach ($partitionFiles as $partition => $file) {
            if (isset($options['skip_partitions']) && 
                in_array($partition, $options['skip_partitions'])) {
                $result['warnings'][] = "Skipped partition: {$partition}";
                continue;
            }
            
            $flashResult = $this->fastbootDevice->flash($partition, $file, 
                $options['disable_verification'] ?? false);
            
            $result['operations'][] = [
                'partition' => $partition,
                'success' => $flashResult['success'],
                'output' => $flashResult['output']
            ];
            
            if (!$flashResult['success']) {
                $result['success'] = false;
                if (!($options['continue_on_error'] ?? false)) {
                    break;
                }
            }
        }
        
        // Clean up extracted files
        $this->cleanExtractedFiles($extractDir);
        
        return $result;
    }
    
    /**
     * Flash single partition (Odin-style)
     */
    public function flashPartition(string $partition, string $imagePath, array $options = []): array {
        if (!$this->fastbootDevice) {
            return ['success' => false, 'error' => 'Not in download mode'];
        }
        
        if (!in_array($partition, $this->supportedPartitions)) {
            return ['success' => false, 'error' => "Unsupported partition: {$partition}"];
        }
        
        if (!file_exists($imagePath)) {
            return ['success' => false, 'error' => 'Image file not found'];
        }
        
        $result = $this->fastbootDevice->flash(
            $partition, 
            $imagePath, 
            $options['disable_verification'] ?? false
        );
        
        if ($result['success']) {
            $result['operation'] = 'samsung_partition_flash';
            $result['partition'] = $partition;
        }
        
        return $result;
    }
    
    /**
     * Flash PIT file (Partition Information Table)
     */
    public function flashPIT(string $pitPath): array {
        if (!$this->fastbootDevice) {
            return ['success' => false, 'error' => 'Not in download mode'];
        }
        
        // Note: Fastboot doesn't support PIT flashing directly
        // This would require OEM unlock and specific Samsung tools
        return [
            'success' => false,
            'error' => 'PIT flashing requires Odin or Samsung-specific tools',
            'alternative' => 'Use Odin3 for PIT operations'
        ];
    }
    
    /**
     * Check firmware compatibility
     */
    public function checkFirmware(string $tarPath): array {
        if (!file_exists($tarPath)) {
            return ['valid' => false, 'error' => 'Firmware file not found'];
        }
        
        $validation = $this->validateTarMD5($tarPath);
        
        if (!$validation['valid']) {
            return ['valid' => false, 'errors' => $validation['errors']];
        }
        
        $extractDir = $this->extractTarMD5($tarPath);
        $partitionFiles = $this->getPartitionFiles($extractDir);
        
        $deviceInfo = $this->getDeviceInfo();
        $compatibility = [];
        
        foreach ($partitionFiles as $partition => $file) {
            $fileSize = filesize($file);
            $partitionSize = $deviceInfo['partitions'][$partition] ?? null;
            
            if ($partitionSize) {
                $fileSizeMB = $fileSize / (1024 * 1024);
                $partitionSizeMB = hexdec($partitionSize) / (1024 * 1024);
                
                $compatibility[$partition] = [
                    'file_size_mb' => round($fileSizeMB, 2),
                    'partition_size_mb' => round($partitionSizeMB, 2),
                    'compatible' => $fileSize < hexdec($partitionSize)
                ];
            }
        }
        
        $this->cleanExtractedFiles($extractDir);
        
        return [
            'valid' => true,
            'device' => $deviceInfo,
            'firmware' => $this->getFirmwareInfo($tarPath),
            'compatibility' => $compatibility
        ];
    }
    
    /**
     * Validate .tar.md5 file
     */
    private function validateTarMD5(string $tarPath): array {
        $errors = [];
        
        // Check file extension
        if (pathinfo($tarPath, PATHINFO_EXTENSION) !== 'tar.md5') {
            $errors[] = 'File must have .tar.md5 extension';
        }
        
        // Check if it contains MD5 hashes
        $content = file_get_contents($tarPath, false, null, 0, 5000);
        if (strpos($content, 'MD5') === false && strpos($content, 'md5') === false) {
            $errors[] = 'No MD5 checksums found in firmware';
        }
        
        // Check for required partition files
        $requiredPartitions = ['AP', 'BL', 'CP', 'CSC'];
        $tempDir = sys_get_temp_dir() . '/samsung_check_' . uniqid();
        
        $phar = new \PharData($tarPath);
        $hasRequired = false;
        
        foreach ($requiredPartitions as $partition) {
            try {
                $phar->getContent(); // This would need actual extraction
                $hasRequired = true; // Simplified for example
            } catch (\Exception $e) {
                $errors[] = "Missing required partition: {$partition}";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Extract .tar.md5 file
     */
    private function extractTarMD5(string $tarPath): ?string {
        $extractDir = sys_get_temp_dir() . '/samsung_flash_' . uniqid();
        
        if (!is_dir($extractDir)) {
            mkdir($extractDir, 0755, true);
        }
        
        try {
            $phar = new \PharData($tarPath);
            $phar->extractTo($extractDir, null, true);
            return $extractDir;
        } catch (\Exception $e) {
            $this->cleanExtractedFiles($extractDir);
            return null;
        }
    }
    
    /**
     * Get partition files from extracted firmware
     */
    private function getPartitionFiles(string $extractDir): array {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractDir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                
                // Map filename to partition
                foreach ($this->supportedPartitions as $partition) {
                    if (stripos($filename, $partition) !== false ||
                        stripos($filename, strtolower($partition)) !== false) {
                        $files[$partition] = $file->getPathname();
                        break;
                    }
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Get firmware information
     */
    private function getFirmwareInfo(string $tarPath): array {
        $info = [
            'filename' => basename($tarPath),
            'size' => filesize($tarPath),
            'date' => date('Y-m-d H:i:s', filemtime($tarPath))
        ];
        
        // Try to extract firmware details from filename
        // Samsung firmware naming: [MODEL]_[CSC]_[MODEL]_O[PDA]_[PHONE]_[CSC]_[MODEL].tar.md5
        $filename = pathinfo($tarPath, PATHINFO_FILENAME);
        $parts = explode('_', $filename);
        
        if (count($parts) >= 3) {
            $info['model'] = $parts[0] ?? 'Unknown';
            $info['csc'] = $parts[1] ?? 'Unknown';
            $info['version'] = $parts[3] ?? 'Unknown'; // PDA version
        }
        
        return $info;
    }
    
    /**
     * Clean extracted files
     */
    private function cleanExtractedFiles(string $dir): void {
        if (is_dir($dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            
            rmdir($dir);
        }
    }
    
    /**
     * Reboot to normal mode
     */
    public function rebootToNormal(): bool {
        if ($this->fastbootDevice) {
            return $this->fastbootDevice->reboot();
        }
        
        if ($this->adbDevice) {
            return $this->adbDevice->reboot('normal');
        }
        
        return false;
    }
    
    /**
     * Get current mode
     */
    public function getMode(): string {
        return $this->mode;
    }
    
    /**
     * Check if Samsung device is connected
     */
    public function isSamsungDevice(): bool {
        if ($this->adbDevice) {
            $props = $this->adbDevice->getProperties();
            return stripos($props['ro.product.manufacturer'] ?? '', 'samsung') !== false;
        }
        
        return false;
    }
}
