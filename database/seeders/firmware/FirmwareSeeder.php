<?php

declare(strict_types=1);

namespace GSMSDK\Database\Seeders\Firmware;

use GSMSDK\Database\Seeder;

/**
 * Firmware Seeder
 */
class FirmwareSeeder extends Seeder {
    /**
     * Run the seeder
     */
    public function run(): void {
        // Create popular firmware for major brands
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(20)
            ->popular()
            ->official()
            ->create();
        
        // Create recommended firmware
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(15)
            ->recommended()
            ->official()
            ->create();
        
        // Create beta firmware
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(10)
            ->beta()
            ->create();
        
        // Create general firmware
        $this->factory(\GSMSDK\Models\Firmware::class)
            ->count(50)
            ->create();
        
        $this->info('Created 95 firmware entries');
    }
    
    /**
     * Output info message
     */
    private function info(string $message): void {
        echo "[INFO] {$message}\n";
    }
}
