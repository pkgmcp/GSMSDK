<?php

declare(strict_types=1);

namespace GSMSDK\Database\Seeders;

use GSMSDK\Database\Seeder;

/**
 * Flash Log Seeder
 */
class FlashLogSeeder extends Seeder {
    /**
     * Run the seeder
     */
    public function run(): void {
        // Create successful flash operations
        $this->factory(\GSMSDK\Models\FlashLog::class)
            ->count(20)
            ->state(['status' => 'completed'])
            ->create();
        
        // Create failed flash operations
        $this->factory(\GSMSDK\Models\FlashLog::class)
            ->count(5)
            ->state(['status' => 'failed'])
            ->create();
        
        // Create pending operations
        $this->factory(\GSMSDK\Models\FlashLog::class)
            ->count(3)
            ->state(['status' => 'pending'])
            ->create();
        
        $this->info('Created 28 flash logs (20 completed, 5 failed, 3 pending)');
    }
    
    /**
     * Output info message
     */
    private function info(string $message): void {
        echo "[INFO] {$message}\n";
    }
}
