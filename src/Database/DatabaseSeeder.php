<?php

declare(strict_types=1);

namespace GSMSDK\Database;

/**
 * DatabaseSeeder
 * 
 * Main database seeder - calls all other seeders.
 */
class DatabaseSeeder extends Seeder {
    /**
     * Seed the database
     */
    public function run(): void {
        // Call all seeders
        $this->callMany([
            UserSeeder::class,
            DeviceSeeder::class,
            FlashLogSeeder::class,
        ]);
    }
}
