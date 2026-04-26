<?php

declare(strict_types=1);

namespace GSMSDK\Database\Seeders\Firmware;

use GSMSDK\Database\Seeder;
use GSMSDK\Models\Firmware;
use GSMSDK\Database\Factories\Firmware\FirmwareFactory;

/**
 * Firmware Seeder
 * 
 * Enhanced with all Xiaomi/Redmi/POCO devices, Nothing phones,
 * Samsung EDL devices, Oukitel devices, and firmware types
 */
class FirmwareSeeder extends Seeder {
    /**
     * Run the seeder
     */
    public function run(): void {
        $this->info('Seeding firmware database...');
        
        // Xiaomi HyperOS devices (new generation)
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(15)
            ->hyperos()
            ->create();
        
        // Xiaomi/Redmi/POCO popular devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(25)
            ->popular()
            ->official()
            ->create();
        
        // Xiaomi-specific devices with IMEI repair support
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(15)
            ->imeiRepair()
            ->official()
            ->create(function (FirmwareFactory $f) {
                $f->xiaomi();
            });
        
        // Xiaomi additional models
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(20)
            ->xiaomi()
            ->create();
        
        // Xiaomi Redmi series additional
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(15)
            ->create(function (FirmwareFactory $f) {
                $f->state(['brand' => 'xiaomi', 'firmware_type' => 'official']);
            });
        
        // Samsung devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(15)
            ->samsung()
            ->popular()
            ->create();
        
        // Nothing phones
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(8)
            ->nothing()
            ->create();
        
        // Oukitel devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(10)
            ->create(function (FirmwareFactory $f) {
                $f->oukitel();
            });
        
        // Recommended firmware across all brands
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(20)
            ->recommended()
            ->official()
            ->create();
        
        // Beta firmware for testing
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(12)
            ->beta()
            ->create();
        
        // FRP remove capable firmware
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(10)
            ->create(function (FirmwareFactory $f) {
                $f->state(['frp_remove_supported' => true, 'imei_repair_supported' => true]);
            });
        
        // Google/Pixel devices - comprehensive factory firmware
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(25)
            ->create(function (FirmwareFactory $f) {
                $models = [
                    'Pixel 8 Pro', 'Pixel 8', 'Pixel 8a',
                    'Pixel 7 Pro', 'Pixel 7', 'Pixel 7a',
                    'Pixel 6 Pro', 'Pixel 6', 'Pixel 6a',
                    'Pixel 5', 'Pixel 5a',
                    'Pixel 4 XL', 'Pixel 4',
                    'Pixel 3 XL', 'Pixel 3', 'Pixel 3a', 'Pixel 3a XL',
                    'Pixel 2 XL', 'Pixel 2',
                    'Pixel XL', 'Pixel',
                    'Pixel Fold', 'Pixel Tablet', 'Pixel Watch', 'Pixel Watch 2'
                ];
                $model = $f->faker()->randomElement($models);
                $version = $f->faker()->randomElement(['15.0', '14.0', '13.0', '12.0', '11.0']);
                $region = $f->faker()->randomElement(['WW', null]);
                $build = 'SD' . strtoupper($f->faker()->bothify('##??##'));
                
                return [
                    'brand' => 'google',
                    'model' => $model,
                    'region' => $region,
                    'version' => $version,
                    'build_number' => $build,
                    'security_patch' => $f->faker()->randomElement(['2025-12-01', '2025-11-01', '2025-10-01']),
                    'android_version' => match(substr($version, 0, 2)) {
                        '15' => '15', '14' => '14', '13' => '13', '12' => '12', '11' => '11', default => '14'
                    },
                    'firmware_type' => 'official',
                    'file_name' => 'google_' . str_replace(' ', '_', strtolower($model)) . '_' . $version . '_' . $build . '_factory_' . strtolower($region ?? 'ww') . '.zip',
                    'file_size' => $f->faker()->randomElement(['2.1GB', '2.3GB', '2.5GB', '2.8GB', '3.0GB']),
                    'file_hash' => $f->faker()->sha256(),
                    'download_url' => 'https://dl.google.com/dl/android/aosp/' . strtolower(str_replace(' ', '_', $model)) . '/' . $version . '/' . $build . '/' . strtolower($region ?? 'ww') . '/' . 'factory_' . strtolower(str_replace(' ', '_', $model)) . '_' . $version . '_' . $build . '_' . strtolower($region ?? 'ww') . '.zip',
                    'changelog' => '+ Factory image for ' . $model . ' ' . $version . "\n" .
                                   '+ Security patch: December 2025' . "\n" .
                                   '+ Android ' . substr($version, 0, 2) . ' update' . "\n" .
                                   '+ Bug fixes and stability improvements' . "\n" .
                                   '+ OTA update available',
                    'status' => 'active',
                    'download_count' => $f->faker()->numberBetween(10000, 100000),
                    'rating' => $f->faker()->numberBetween(4, 5),
                    'is_popular' => true,
                    'is_recommended' => true,
                    'imei_repair_supported' => false,
                    'flash_mode_supported' => true,
                    'adb_mode_supported' => true,
                    'frp_remove_supported' => false,
                    'camera_sms_working' => true,
                    'ota_supported' => true,
                    'factory_reset_safe' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
        
        // Google/Pixel OTA updates
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(15)
            ->create(function (FirmwareFactory $f) {
                $models = ['Pixel 8 Pro', 'Pixel 8', 'Pixel 7 Pro', 'Pixel 7', 'Pixel 6 Pro', 'Pixel 6'];
                $model = $f->faker()->randomElement($models);
                $versions = ['15.0.1', '15.0.2', '14.0.5', '14.0.6', '13.0.8', '13.0.9'];
                $version = $f->faker()->randomElement($versions);
                
                return [
                    'brand' => 'google',
                    'model' => $model,
                    'region' => null,
                    'version' => $version,
                    'build_number' => 'SD' . strtoupper($f->faker()->bothify('##??##')),
                    'security_patch' => '2025-12-01',
                    'android_version' => substr($version, 0, 2),
                    'firmware_type' => 'official',
                    'file_name' => 'ota_' . str_replace(' ', '_', strtolower($model)) . '_' . $version . '_update.zip',
                    'file_size' => $f->faker()->randomElement(['150MB', '180MB', '200MB', '250MB']),
                    'file_hash' => $f->faker()->sha256(),
                    'download_url' => 'https://dl.google.com/dl/android/ota/' . strtolower(str_replace(' ', '_', $model)) . '/' . $version . '/ota_update.zip',
                    'changelog' => '+ OTA update ' . $version . "\n" .
                                   '+ Security improvements' . "\n" .
                                   '+ Bug fixes' . "\n" .
                                   '+ Incremental update',
                    'status' => 'active',
                    'download_count' => $f->faker()->numberBetween(50000, 200000),
                    'rating' => $f->faker()->numberBetween(4, 5),
                    'is_popular' => true,
                    'is_recommended' => true,
                    'imei_repair_supported' => false,
                    'flash_mode_supported' => false,
                    'adb_mode_supported' => true,
                    'frp_remove_supported' => false,
                    'camera_sms_working' => true,
                    'ota_supported' => true,
                    'factory_reset_safe' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
        
        // General firmware entries (various brands/models)
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(25)
            ->create();
        
        // Add specific known devices from changelog
        $specificDevices = [
            // Xiaomi/Redmi/POCO new devices
            ['tornado', 'xiaomi', 'Redmi 15C 5G / Redmi 15R 5G / POCO C85 5G'],
            ['malachite', 'xiaomi', 'Redmi Note 14 Pro 5G / POCO X7'],
            ['rodin', 'xiaomi', 'Redmi Turbo 4 / POCO X7 Pro'],
            ['klimt', 'xiaomi', 'Xiaomi 15T Pro'],
            ['goya', 'xiaomi', 'Xiaomi 15T'],
            ['obsidian', 'xiaomi', 'Redmi Note 14 Pro 4G'],
            ['tanzantite', 'xiaomi', 'Redmi Note 14 4G'],
            ['emerald', 'xiaomi', 'Redmi Note 13 Pro 4G / POCO M6 Pro'],
            ['degas', 'xiaomi', 'Xiaomi 14T'],
            ['corot', 'xiaomi', 'Redmi K60 Ultra / Xiaomi 13T Pro'],
            ['plato', 'xiaomi', 'Xiaomi 12T'],
            ['aristotle', 'xiaomi', 'Xiaomi 13T'],
            ['zircon', 'xiaomi', 'Redmi Note 13 Pro+ 5G'],
            ['xaga', 'xiaomi', 'Redmi Note 11T Pro / Redmi Note 11T Pro+ / POCO X4 GT'],
            ['air', 'xiaomi', 'Redmi 13R 5G / Redmi 13C 5G'],
            ['moon', 'xiaomi', 'Redmi 13'],
            ['tides', 'xiaomi', 'Redmi 13'],
            ['pond', 'xiaomi', 'Redmi 14C / POCO C75'],
            ['lake', 'xiaomi', 'Redmi 14C / POCO C75'],
            ['blue', 'xiaomi', 'Redmi A3'],
            ['dew', 'xiaomi', 'Redmi 15C'],
            ['gale', 'xiaomi', 'Redmi 13C / POCO C65'],
            ['gust', 'xiaomi', 'Redmi 13C / POCO C65'],
            
            // Nothing phones
            ['A001T', 'nothing', 'Nothing Phone (3a) Lite'],
            ['A001', 'nothing', 'Nothing CMF Phone 2 Pro'],
            ['A142P', 'nothing', 'Nothing Phone (2a) Plus'],
            ['A015', 'nothing', 'Nothing CMF Phone 1'],
            ['A142', 'nothing', 'Nothing Phone (2a)'],
            
            // Samsung devices
            ['SM-A235M', 'samsung', 'Galaxy A23'],
            ['SM-M515F', 'samsung', 'Galaxy M51'],
            ['SM-M556E', 'samsung', 'Galaxy M55 5G'],
            ['SM-S926U', 'samsung', 'Galaxy S24+'],
        ];
        
        foreach ($specificDevices as $device) {
            Firmware::create([
                'brand' => $device[1],
                'model' => $device[0],
                'model_display' => $device[2],
                'region' => $device[1] === 'samsung' ? 'WW' : null,
                'version' => match($device[1]) {
                    'xiaomi' => '14.0',
                    'nothing' => '5.6',
                    'samsung' => '14.0',
                    default => '13.0'
                },
                'build_number' => 'RP1A.20241201.001',
                'security_patch' => '2025-12-01',
                'android_version' => match($device[1]) {
                    'samsung' => '14',
                    default => '14'
                },
                'firmware_type' => 'official',
                'file_name' => strtoupper($device[1]) . '_' . $device[0] . '_V14.0_FIRMWARE' . ($device[1] === 'samsung' ? '.tar.md5' : '.zip'),
                'file_size' => $device[1] === 'samsung' ? '2.5GB' : '1.5GB',
                'file_hash' => hash('sha256', $device[0] . microtime()),
                'download_url' => 'https://downloads.example.com/' . $device[1] . '/' . $device[0] . '/',
                'changelog' => $this->generateSpecificChangelog($device[1]),
                'status' => 'active',
                'download_count' => rand(1000, 20000),
                'rating' => rand(4, 5),
                'is_popular' => true,
                'is_recommended' => true,
                'imei_repair_supported' => true,
                'flash_mode_supported' => true,
                'adb_mode_supported' => true,
                'frp_remove_supported' => $device[1] !== 'samsung',
                'camera_sms_working' => true,
                'ota_supported' => true,
                'factory_reset_safe' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->info('Seeded firmware database with comprehensive device coverage');
        $this->info('Total firmware entries: ' . Firmware::count());
    }
    
    /**
     * Generate changelog for specific device
     */
    private function generateSpecificChangelog(string $brand): string {
        $changes = [
            'Security patch update to December 2025',
            'HyperOS 1/2/3 full compatibility',
            'IMEI repair via Flash Mode & ADB Mode',
            'Camera & SMS fully functional after repair',
            'Factory Reset supported (IMEI preserved)',
            'OTA/System Update not supported after repair',
            'Enhanced Flash Mode communication stability',
            'Reliable ADB flashing process',
            'Latest security patches applied',
            'FRP removal capability included',
            'Partition Manager enhancements',
            '60fps real-time progress updates',
            'WebUSB dashboard integration'
        ];
        
        $selected = array_slice($changes, 0, rand(4, 7));
        
        return implode("\n\n", array_map(function ($change) {
            return '+ ' . $change;
        }, $selected));
    }
    
    /**
     * Output info message
     */
    private function info(string $message): void {
        echo "[INFO] {$message}\n";
    }
}
