<?php

declare(strict_types=1);

namespace GSMSDK\Database\Factories\Firmware;

use GSMSDK\Database\Factory;
use GSMSDK\Models\Firmware;

/**
 * Firmware Factory
 * 
 * Enhanced with all Xiaomi/Redmi/POCO devices, Nothing phones,
 * Samsung EDL devices, and Oukitel devices
 */
class FirmwareFactory extends Factory {
    // Xiaomi/Redmi/POCO devices (including new additions)
    private array $xiaomiDevices = [
        'tornado',     // Redmi 15C 5G / Redmi 15R 5G / POCO C85 5G
        'malachite',   // Redmi Note 14 Pro 5G / POCO X7
        'rodin',       // Redmi Turbo 4 / POCO X7 Pro
        'klimt',       // Xiaomi 15T Pro
        'goya',        // Xiaomi 15T
        'obsidian',    // Redmi Note 14 Pro 4G
        'tanzantite',  // Redmi Note 14 4G
        'emerald',     // Redmi Note 13 Pro 4G / POCO M6 Pro / Redmi Note 14S
        'degas',       // Xiaomi 14T
        'corot',       // Redmi K60 Ultra / Xiaomi 13T Pro
        'plato',       // Xiaomi 12T
        'aristotle',   // Xiaomi 13T
        'zircon',      // Redmi Note 13 Pro+ 5G
        'xaga',        // Redmi Note 11T Pro / Redmi Note 11T Pro+ / POCO X4 GT / Redmi K50i
        'air',         // Redmi 13R 5G / Redmi 13C 5G / POCO M6 5G
        'moon',        // Redmi 13
        'tides',       // Redmi 13
        'pond',        // Redmi 14C / POCO C75 / A3 Pro
        'lake',        // Redmi 14C / POCO C75 / A3 Pro
        'blue',        // Redmi A3
        'dew',         // Redmi 15C
        'gale',        // Redmi 13C / POCO C65
        'gust',        // Redmi 13C / POCO C65
    ];
    
    // Nothing phones
    private array $nothingDevices = [
        'A001T',      // Nothing Phone (3a) Lite (MT6878)
        'A001',       // Nothing CMF Phone 2 Pro (MT6878)
        'A142P',      // Nothing Phone (2a) Plus (MT6886)
        'A015',       // Nothing CMF Phone 1 (MT6878)
        'A142',       // Nothing Phone (2a) (MT6886)
    ];
    
    // Samsung devices
    private array $samsungDevices = [
        'SM-A235M',   // Galaxy A23 [BIT-B]
        'SM-M515F',   // Galaxy M51 [BIT-6]
        'SM-M556E',   // Galaxy M55 5G [BIT-4]
        'SM-S926U',   // Galaxy S24+ [BIT-4]
        'SM-S918B',   // Galaxy S24 Ultra
        'SM-S911U',   // Galaxy S24
        'SM-S906B',   // Galaxy S23
    ];
    
    // Oukitel devices
    private array $oukitelDevices = [
        'WP55S', 'P1PRO', 'P1', 'RT8', 'OT5',
        'WP27', 'WP21ULTRA', 'WP52', 'WP39', 'WP50',
        'C50', 'WP35', 'WP33PRO', 'C6', 'WP39PRO',
        'WP35PRO', 'WP60', 'WP62', 'WP55ULTRA', 'WP300',
        'WP55', 'WP55PRO', 'WP100TITAN', 'RT10INDUSTRY'
    ];
    
    // Generic brands
    private array $brands = [
        'xiaomi', 'redmi', 'poco', 'nothing', 'samsung',
        'oukitel', 'google', 'asus', 'motorola', 'lg',
        'nokia', 'sony', 'huawei', 'oneplus', 'oppo', 'vivo'
    ];
    
    private array $regions = ['WW', 'CN', 'EU', 'IN', 'US', 'KR', 'TW', 'RU', 'BR', null];
    private array $versions = [
        '15.0', '14.0', '13.0', '12.5', '12.0',
        '11.0', '10.0', '9.0', 'HyperOS 1', 'HyperOS 2', 'HyperOS 3'
    ];
    private array $types = ['official', 'official', 'official', 'beta', 'stock', 'hyperos'];
    private array $status = ['active', 'active', 'active', 'beta', 'active'];
    private array $securityPatches = [
        '2025-12-01', '2025-11-01', '2025-10-01', '2025-09-01',
        '2025-08-01', '2025-07-01', '2025-06-01', '2025-05-01',
        '2025-04-01', '2025-03-01', '2025-02-01', '2025-01-01',
        '2024-12-01', '2024-11-01', '2024-10-01', '2024-09-01',
        '2026-01-01', '2026-02-01'
    ];
    private array $androidVersions = ['15', '14', '13', '12', '11', '10', '9'];
    
    /**
     * Define default attributes
     */
    protected function definition(): array {
        $brand = $this->faker()->randomElement($this->brands);
        $model = $this->getRandomModel($brand);
        $version = $this->faker()->randomElement($this->versions);
        $type = $this->faker()->randomElement($this->types);
        $region = $this->faker()->randomElement($this->regions);
        $securityPatch = $this->faker()->randomElement($this->securityPatches);
        $androidVersion = $this->faker()->randomElement($this->androidVersions);
        $firmwareType = $type === 'hyperos' ? 'hyperos' : $type;
        
        return [
            'brand' => $brand,
            'model' => $model,
            'region' => $region,
            'version' => $version,
            'build_number' => $this->generateBuildNumber(),
            'security_patch' => $securityPatch,
            'android_version' => $androidVersion,
            'firmware_type' => $firmwareType,
            'file_name' => $this->generateFileName($brand, $model, $version, $region, $firmwareType),
            'file_size' => $this->generateFileSize($type),
            'file_hash' => $this->faker()->sha256(),
            'download_url' => $this->generateDownloadUrl($brand, $model, $version),
            'changelog' => $this->generateChangelog($firmwareType),
            'status' => $this->faker()->randomElement($this->status),
            'download_count' => $this->faker()->numberBetween(0, 100000),
            'rating' => $this->faker()->numberBetween(1, 5),
            'is_popular' => $this->faker()->boolean(20),
            'is_recommended' => $this->faker()->boolean(10),
            'imei_repair_supported' => $this->faker()->boolean(30),
            'flash_mode_supported' => true,
            'adb_mode_supported' => true,
            'frp_remove_supported' => $this->faker()->boolean(25),
            'camera_sms_working' => $this->faker()->boolean(90),
            'ota_supported' => $firmwareType !== 'hyperos' ? true : $this->faker()->boolean(50),
            'factory_reset_safe' => $this->faker()->boolean(85)
        ];
    }
    
    /**
     * Get random model based on brand
     */
    private function getRandomModel(string $brand): string {
        return match($brand) {
            'xiaomi', 'redmi', 'poco' => $this->faker()->randomElement($this->xiaomiDevices),
            'nothing' => $this->faker()->randomElement($this->nothingDevices),
            'samsung' => $this->faker()->randomElement($this->samsungDevices),
            'oukitel' => $this->faker()->randomElement($this->oukitelDevices),
            default => $this->generateGenericModel($brand)
        };
    }
    
    /**
     * Generate generic model name
     */
    private function generateGenericModel(string $brand): string {
        $prefixes = ['Pro', 'Max', 'Ultra', 'Lite', 'Plus', 'Prime', 'Neo', 'Edge', 'Fold', 'Flip'];
        $suffixes = ['Series', 'Device', 'Phone', 'Smart', 'Tab', 'Note', 'View', 'Pad'];
        $numbers = [9, 10, 11, 12, 13, 14, 15, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500, 1000];
        
        $pattern = $this->faker()->randomElement([
            $brand . ' ' . $this->faker()->randomElement($numbers),
            $brand . ' ' . $this->faker()->randomElement($prefixes) . ' ' . $this->faker()->randomElement($numbers),
            $brand . ' ' . $this->faker()->randomElement($numbers) . ' ' . $this->faker()->randomElement($suffixes),
        ]);
        
        return $pattern;
    }
    
    /**
     * Generate build number
     */
    private function generateBuildNumber(): string {
        $patterns = [
            strtoupper($this->faker()->bothify('##??##')),
            strtoupper($this->faker()->bothify('???##')),
            strtoupper($this->faker()->bothify('##???##')),
            'RP1A.' . strtoupper($this->faker()->bothify('?????')) . '.' . $this->faker()->numberBetween(100, 999),
            'V' . $this->faker()->numberBetween(10, 50) . 'R1.0',
        ];
        return $this->faker()->randomElement($patterns);
    }
    
    /**
     * Generate file name
     */
    private function generateFileName(string $brand, string $model, string $version, ?string $region, string $type): string {
        $regionStr = $region ? '_' . $region : '';
        $brandUpper = strtoupper($brand);
        $modelUpper = str_replace(' ', '_', strtoupper($model));
        $versionClean = str_replace([' ', '.'], ['_', 'V'], $version);
        
        $extensions = ['.zip', '.tar.md5', '.tgz', '.rar', '.7z', '.bin', '.pac', '.rom', '.img'];
        
        if ($brand === 'samsung') {
            $ext = '.tar.md5';
        } elseif ($brand === 'xiaomi' || $brand === 'redmi' || $brand === 'poco') {
            $ext = $this->faker()->randomElement(['.zip', '.tgz', '.rar']);
        } elseif ($brand === 'nothing') {
            $ext = '.zip';
        } else {
            $ext = $this->faker()->randomElement($extensions);
        }
        
        if ($type === 'hyperos') {
            return "HYPEROS_${brandUpper}_${modelUpper}_${versionClean}${regionStr}_ALL${ext}";
        }
        
        if ($brand === 'xiaomi' || $brand === 'redmi') {
            return "${brandUpper}_${modelUpper}_${versionClean}_${regionStr}_FIRMWARE${ext}";
        }
        
        return "${brandUpper}_${modelUpper}_${versionClean}${regionStr}_FIRMWARE${ext}";
    }
    
    /**
     * Generate file size
     */
    private function generateFileSize(string $type): string {
        $baseSizes = [
            'official' => [512, 1024, 1536, 2048, 2560, 3072],
            'beta' => [512, 1024, 1536],
            'stock' => [256, 512, 1024],
            'hyperos' => [2048, 3072, 4096],
        ];
        
        $sizes = $baseSizes[$type] ?? $baseSizes['official'];
        $sizeMB = $this->faker()->randomElement($sizes);
        
        if ($sizeMB >= 1024) {
            $gb = round($sizeMB / 1024, 1);
            return $gb . 'GB';
        }
        
        return $sizeMB . 'MB';
    }
    
    /**
     * Generate download URL
     */
    private function generateDownloadUrl(string $brand, string $model, string $version): string {
        $baseUrls = [
            'xiaomi' => 'https://update.miui.com/updates/miota-fullrom.php',
            'redmi' => 'https://update.miui.com/updates/miota-fullrom.php',
            'poco' => 'https://update.miui.com/updates/miota-fullrom.php',
            'nothing' => 'https://downloads.nothing.tech/',
            'samsung' => 'https://samfw.com/f/',
            'oukitel' => 'https://www.oukitel.com/support/',
            'google' => 'https://dl.google.com/dl/android/aosp/',
            'asus' => 'https://www.asus.com/support/',
            'nokia' => 'https://firmwarefile.com/',
        ];
        
        if (isset($baseUrls[$brand])) {
            $baseUrl = $baseUrls[$brand];
            return $baseUrl . '/' . strtoupper($brand) . '/' . $model . '/' . $version . '/';
        }
        
        return 'https://firmware.example.com/' . $brand . '/' . $model . '/' . $version . '/';
    }
    
    /**
     * Generate changelog
     */
    private function generateChangelog(string $type): string {
        $commonChanges = [
            'Security patch update to latest level',
            'Bug fixes and stability improvements',
            'Performance optimization and system tuning',
            'Camera improvements and HDR enhancements',
            'Battery optimization and power management',
            'UI/UX enhancements and animations',
            'Network stability and connectivity fixes',
            'Audio quality improvements and noise reduction',
            'Display optimizations and color accuracy',
            'Fingerprint sensor performance improvements',
            'Face unlock speed and accuracy improvements',
            'Gestures and navigation enhancements',
            'Multi-camera switching improvements'
        ];
        
        $typeSpecific = [
            'hyperos' => [
                'HyperOS core system upgrade',
                'Cross-device connectivity improvements',
                'AI features and smart assistant enhancements',
                'New icon pack and visual redesign',
                'Enhanced privacy controls',
                'Improved gaming mode and performance'
            ],
            'beta' => [
                'Beta testing features enabled',
                'Experimental features included',
                'Developer options enhancements',
                'Early access to new UI elements'
            ],
            'official' => [
                'OTA update delivery optimization',
                'Carrier-specific optimizations',
                'Regional feature enhancements',
                'Certification and compliance updates'
            ]
        ];
        
        $changes = $commonChanges;
        if (isset($typeSpecific[$type])) {
            $changes = array_merge($changes, $typeSpecific[$type]);
        }
        
        $count = $this->faker()->numberBetween(3, 6);
        $selected = $this->faker()->randomElements($changes, min($count, count($changes)));
        
        $changelog = "Changelog for version:\n\n";
        $changelog .= implode("\n\n", array_map(function ($change) {
            return '+ ' . $change;
        }, $selected));
        
        $changelog .= "\n\n\nKnown Issues:\n";
        $changelog .= '- Minor UI glitches in settings menu';
        
        return $changelog;
    }
    
    /**
     * Create model instance
     */
    protected function createModel(array $attributes): mixed {
        $firmware = new Firmware($attributes);
        $firmware->save();
        return $firmware;
    }
    
    /**
     * Make model instance without persisting
     */
    protected function makeModel(array $attributes): mixed {
        return new Firmware($attributes);
    }
    
    /**
     * Set as popular
     */
    public function popular(): static {
        return $this->state([
            'is_popular' => true, 
            'download_count' => $this->faker()->numberBetween(5000, 50000),
            'rating' => $this->faker()->numberBetween(4, 5)
        ]);
    }
    
    /**
     * Set as recommended
     */
    public function recommended(): static {
        return $this->state(['is_recommended' => true]);
    }
    
    /**
     * Set as beta
     */
    public function beta(): static {
        return $this->state(['firmware_type' => 'beta', 'status' => 'beta']);
    }
    
    /**
     * Set as official
     */
    public function official(): static {
        return $this->state(['firmware_type' => 'official', 'status' => 'active']);
    }
    
    /**
     * Set as HyperOS
     */
    public function hyperos(): static {
        return $this->state([
            'firmware_type' => 'hyperos',
            'status' => 'active',
            'ota_supported' => false,
            'camera_sms_working' => true
        ]);
    }
    
    /**
     * Set as IMEI repair supported
     */
    public function imeiRepair(): static {
        return $this->state([
            'imei_repair_supported' => true,
            'frp_remove_supported' => true,
            'factory_reset_safe' => true
        ]);
    }
    
    /**
     * Set as Xiaomi device
     */
    public function xiaomi(): static {
        return $this->state([
            'brand' => 'xiaomi',
            'firmware_type' => $this->faker()->randomElement(['official', 'hyperos']),
            'region' => $this->faker()->randomElement(['CN', 'TW', 'WW', 'IN', 'RU', 'BR', null])
        ]);
    }
    
    /**
     * Set as Samsung device
     */
    public function samsung(): static {
        return $this->state([
            'brand' => 'samsung',
            'firmware_type' => 'official',
            'region' => $this->faker()->randomElement(['WW', 'KR', 'IN', 'US', null])
        ]);
    }
    
    /**
     * Set as Nothing device
     */
    public function nothing(): static {
        return $this->state([
            'brand' => 'nothing',
            'firmware_type' => 'official',
            'region' => null
        ]);
    }
}
