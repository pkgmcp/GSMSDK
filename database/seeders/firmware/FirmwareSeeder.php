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
        
        // Google/Pixel devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(8)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'google', 
                    'model' => $f->faker()->randomElement(['Pixel 8', 'Pixel 8 Pro', 'Pixel 7', 'Pixel 7a', 'Pixel 6a']),
                    'firmware_type' => 'official',
                    'region' => null
                ]);
            });
        
        // Motorola devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(8)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'motorola',
                    'model' => $f->faker()->randomElement(['Edge 40', 'Razr 40', 'Moto G84', 'G Power 2023']),
                    'firmware_type' => 'official'
                ]);
            });
        
        // ASUS devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(6)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'asus',
                    'model' => $f->faker()->randomElement(['Zenfone 10', 'ROG Phone 7', 'Zenfone 11']),
                    'firmware_type' => 'official'
                ]);
            });
        
        // Sony devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(6)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'sony',
                    'model' => $f->faker()->randomElement(['Xperia 1 V', 'Xperia 10 V', 'Xperia 5 V']),
                    'firmware_type' => 'official',
                    'region' => null
                ]);
            });
        
        // Huawei devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(6)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'huawei',
                    'model' => $f->faker()->randomElement(['P60', 'Mate 50', 'Mate 60', 'P70']),
                    'firmware_type' => 'official',
                    'region' => 'CN'
                ]);
            });
        
        // OnePlus devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(6)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'oneplus',
                    'model' => $f->faker()->randomElement(['11', '11R', 'Nord 3', '12', 'Open']),
                    'firmware_type' => 'official',
                    'region' => null
                ]);
            });
        
        // OPPO devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(6)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'oppo',
                    'model' => $f->faker()->randomElement(['Find X6', 'Reno 10', 'Reno 11', 'A1', 'A78']),
                    'firmware_type' => 'official'
                ]);
            });
        
        // Vivo devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(6)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'vivo',
                    'model' => $f->faker()->randomElement(['X90', 'X90 Pro', 'V29', 'V30', 'Y100']),
                    'firmware_type' => 'official'
                ]);
            });
        
        // LG devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(5)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'lg',
                    'model' => $f->faker()->randomElement(['Wing', 'Velvet 5G', 'G8X', 'V60', 'K40']),
                    'firmware_type' => 'official'
                ]);
            });
        
        // Nokia devices
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(5)
            ->create(function (FirmwareFactory $f) {
                $f->state([
                    'brand' => 'nokia',
                    'model' => $f->faker()->randomElement(['G50', 'X30', 'G42', 'C32', 'G22']),
                    'firmware_type' => 'official'
                ]);
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
            ['air', 'xiaomi', 'Redmi 13R 5G / Redmi 13C 5G / POCO M6 5G'],
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
