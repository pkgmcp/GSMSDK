<?php

declare(strict_types=1);

namespace GSMSDK\Database\Seeders;

use GSMSDK\Database\Seeder;

/**
 * Device Seeder
 */
class DeviceSeeder extends Seeder {
    /**
     * Run the seeder
     */
    public function run(): void {
        // Create ADB devices
        $this->factory(\GSMSDK\Models\Device::class)
            ->count(5)
            ->state(['type' => 'adb', 'authorized' => true, 'online' => true])
            ->create();
        
        // Create unauthorized devices
        $this->factory(\GSMSDK\Models\Device::class)
            ->count(2)
            ->state(['authorized' => false, 'state' => 'unauthorized'])
            ->create();
        
        // Create offline devices
        $this->factory(\GSMSDK\Models\Device::class)
            ->count(3)
            ->state(['online' => false, 'state' => 'offline'])
            ->create();
        
        // Create fastboot devices
        $this->factory(\GSMSDK\Models\Device::class)
            ->count(2)
            ->state(['type' => 'fastboot'])
            ->create();
        
        $this->info('Created 12 devices (5 ADB, 2 unauthorized, 3 offline, 2 fastboot)');
    }
    
    /**
     * Output info message
     */
    private function info(string $message): void {
        echo "[INFO] {$message}\n";
    }
}
