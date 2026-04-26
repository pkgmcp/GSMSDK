<?php

declare(strict_types=1);

namespace GSMSDK\Database\Seeders;

use GSMSDK\Database\Seeder;

/**
 * User Seeder
 */
class UserSeeder extends Seeder {
    /**
     * Run the seeder
     */
    public function run(): void {
        // Create admin user
        $this->factory(\GSMSDK\Models\User::class)
            ->count(1)
            ->state([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'is_admin' => true,
                'active' => true,
            ])
            ->create();
        
        // Create regular users
        $this->factory(\GSMSDK\Models\User::class)
            ->count(10)
            ->create();
        
        $this->info('Created 11 users (1 admin, 10 regular)');
    }
    
    /**
     * Output info message
     */
    private function info(string $message): void {
        echo "[INFO] {$message}\n";
    }
}
