<?php

declare(strict_types=1);

namespace GSMSDK\WebUSB;

/**
 * TarMd5 Extractor
 * 
 * Extracts and validates Samsung .tar.md5 firmware packages.
 */
class TarMd5Extractor {
    private string $tarMd5Path;
    private array $partitions = [];
    private array $validationErrors = [];
    
    public function __construct(string $tarMd5Path) {
        $this->tarMd5Path = $tarMd5Path;
    }
    
    /**
     * Extract firmware package
     */
    public function extract(): array {
        // Validate file
        if (!$this->validateFile()) {
            throw new \RuntimeException('Invalid firmware package: ' . implode(', ', $this->validationErrors));
        }
        
        // Create extraction directory
        $extractDir = $this->createExtractionDirectory();
        
        // Extract tar archive
        $extractedFiles = $this->extractTar($extractDir);
        
        // Validate MD5 checksums
        if (!$this->validateMd5Checksums($extractedFiles)) {
            throw new \RuntimeException('MD5 checksum validation failed');
        }
        
        // Map files to partitions
        $this->partitions = $this->mapPartitions($extractedFiles);
        
        return $this->partitions;
    }
    
    /**
     * Validate firmware file
     */
    private function validateFile(): bool {
        $valid = true;
        
        // Check file exists
        if (!file_exists($this->tarMd5Path)) {
            $this->validationErrors[] = 'File not found';
            $valid = false;
        }
        
        // Check file extension
        if (pathinfo($this->tarMd5Path, PATHINFO_EXTENSION) !== 'tar.md5') {
            $this->validationErrors[] = 'Invalid file extension (must be .tar.md5)';
            $valid = false;
        }
        
        // Check file size (minimum 1MB)
        if (filesize($this->tarMd5Path) < 1024 * 1024) {
            $this->validationErrors[] = 'File too small to be valid firmware';
            $valid = false;
        }
        
        // Check for MD5 signatures in first few KB
        $header = file_get_contents($this->tarMd5Path, false, null, 0, 8192);
        if (strpos($header, 'MD5') === false && strpos($header, 'md5') === false) {
            $this->validationErrors[] = 'No MD5 signatures found';
            $valid = false;
        }
        
        return $valid;
    }
    
    /**
     * Create extraction directory
     */
    private function createExtractionDirectory(): string {
        $baseDir = sys_get_temp_dir() . '/gsmsdk_samsung_' . uniqid();
        
        if (!mkdir($baseDir, 0755, true) && !is_dir($baseDir)) {
            throw new \RuntimeException('Failed to create extraction directory');
        }
        
        return $baseDir;
    }
    
    /**
     * Extract tar archive
     */
    private function extractTar(string $extractDir): array {
        $extractedFiles = [];
        
        try {
            // Use PharData to extract tar
            $phar = new \PharData($this->tarMd5Path);
            $phar->extractTo($extractDir, null, true);
            
            // Get list of extracted files
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $extractedFiles[] = $file->getPathname();
                }
            }
        } catch (\Exception $e) {
            // Clean up on failure
            $this->cleanExtractionDirectory($extractDir);
            throw new \RuntimeException('Failed to extract firmware: ' . $e->getMessage());
        }
        
        return $extractedFiles;
    }
    
    /**
     * Validate MD5 checksums
     */
    private function validateMd5Checksums(array $files): bool {
        // Read MD5 signatures from tar.md5 file
        $signatures = $this->extractMd5Signatures();
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            // Skip .md5 files themselves
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'md5') {
                continue;
            }
            
            // Find corresponding MD5 signature
            $expectedMd5 = $this->findMd5Signature($filename, $signatures);
            
            if ($expectedMd5) {
                // Calculate actual MD5
                $actualMd5 = md5_file($file);
                
                if ($actualMd5 !== $expectedMd5) {
                    $this->validationErrors[] = "MD5 mismatch for {$filename}";
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Extract MD5 signatures from tar.md5 file
     */
    private function extractMd5Signatures(): array {
        $signatures = [];
        
        // Read file in chunks looking for MD5 lines
        $handle = fopen($this->tarMd5Path, 'r');
        $buffer = '';
        $maxRead = 100000; // Read first 100KB
        $read = 0;
        
        while (!feof($handle) && $read < $maxRead) {
            $chunk = fread($handle, 8192);
            $buffer .= $chunk;
            $read += strlen($chunk);
        }
        
        fclose($handle);
        
        // Parse MD5 signatures
        $lines = explode("\n", $buffer);
        foreach ($lines as $line) {
            // Match MD5 signatures (format: "filename MD5hash" or "MD5hash  filename")
            if (preg_match('/([a-f0-9]{32})[\s*]+(.+)$/i', $line, $matches)) {
                $filename = trim($matches[2]);
                $signatures[$filename] = strtolower($matches[1]);
            } elseif (preg_match('/^([a-f0-9]{32})\s+(.+)$/i', $line, $matches)) {
                $filename = trim($matches[2]);
                $signatures[$filename] = strtolower($matches[1]);
            }
        }
        
        return $signatures;
    }
    
    /**
     * Find MD5 signature for file
     */
    private function findMd5Signature(string $filename, array $signatures): ?string {
        // Direct match
        if (isset($signatures[$filename])) {
            return $signatures[$filename];
        }
        
        // Try with path variations
        foreach ($signatures as $sigFile => $md5) {
            if (basename($sigFile) === $filename) {
                return $md5;
            }
        }
        
        return null;
    }
    
    /**
     * Map extracted files to partitions
     */
    private function mapPartitions(array $files): array {
        $partitions = [];
        
        // Common Samsung partition naming patterns
        $partitionPatterns = [
            'boot' => ['boot', 'BOOT', 'recovery', 'RECOVERY'],
            'system' => ['system', 'SYSTEM', 'AP'],
            'vendor' => ['vendor', 'VENDOR'],
            'product' => ['product', 'PRODUCT'],
            'csc' => ['CSC', 'csc', 'home'],
            'cache' => ['cache', 'CACHE'],
            'userdata' => ['userdata', 'USERDATA'],
            'bootloader' => ['bootloader', 'BL'],
            'modem' => ['modem', 'CP', 'RADIO'],
            'vbmeta' => ['vbmeta', 'VBMETA'],
            'dtbo' => ['dtbo', 'DTBO'],
            'odm' => ['odm', 'ODM'],
            'oem' => ['oem', 'OEM'],
        ];
        
        foreach ($files as $file) {
            $filename = basename($file);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Skip .md5 files
            if ($extension === 'md5') {
                continue;
            }
            
            // Try to identify partition
            $partitionName = null;
            
            foreach ($partitionPatterns as $partition => $patterns) {
                foreach ($patterns as $pattern) {
                    if (stripos($filename, $pattern) !== false) {
                        $partitionName = $partition;
                        break 2;
                    }
                }
            }
            
            // Default to filename without extension
            if (!$partitionName) {
                $partitionName = pathinfo($filename, PATHINFO_FILENAME);
            }
            
            $partitions[$partitionName] = $file;
        }
        
        return $partitions;
    }
    
    /**
     * Get firmware information
     */
    public function getFirmwareInfo(): array {
        $filename = basename($this->tarMd5Path);
        $fileSize = filesize($this->tarMd5Path);
        
        // Parse filename for Samsung firmware info
        // Format: [MODEL]_[CSC]_[MODEL]_O[PDA]_[PHONE]_[CSC]_[MODEL].tar.md5
        $parts = explode('_', pathinfo($filename, PATHINFO_FILENAME));
        
        $info = [
            'filename' => $filename,
            'size' => $fileSize,
            'size_mb' => round($fileSize / (1024 * 1024), 2),
            'model' => $parts[0] ?? 'Unknown',
            'csc' => $parts[1] ?? 'Unknown',
            'pda_version' => $parts[3] ?? 'Unknown',
            'phone_version' => $parts[4] ?? 'Unknown',
            'date' => date('Y-m-d H:i:s', filemtime($this->tarMd5Path))
        ];
        
        return $info;
    }
    
    /**
     * Get validation errors
     */
    public function getValidationErrors(): array {
        return $this->validationErrors;
    }
    
    /**
     * Get partition count
     */
    public function getPartitionCount(): int {
        return count($this->partitions);
    }
    
    /**
     * Get partitions
     */
    public function getPartitions(): array {
        return $this->partitions;
    }
    
    /**
     * Clean extraction directory
     */
    private function cleanExtractionDirectory(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }
        
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
    
    /**
     * Destructor - clean up
     */
    public function __destruct() {
        // Clean up any remaining extraction directories
    }
}
