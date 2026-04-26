<?php

declare(strict_types=1);

namespace GSMSDK\Database\Factories;

use GSMSDK\Database\Factory;
use GSMSDK\Models\Device;

/**
 * Device Factory
 */
class DeviceFactory extends Factory {
    /**
     * Define the model's default state
     */
    protected function definition(): array {
        $faker = \Faker\Factory::create();
        
        return [
            'serial' => $faker->unique()->bothify('????????????'),
            'model' => $faker->randomElement(['Pixel 7', 'Pixel 6', 'Galaxy S22', 'Galaxy S23']),
            'product' => $faker->randomElement(['oriole', 'raven', 'bluejay', 'panther']),
            'manufacturer' => $faker->randomElement(['Google', 'Samsung', 'OnePlus', 'Xiaomi']),
            'state' => $faker->randomElement(['device', 'unauthorized', 'offline']),
            'type' => $faker->randomElement(['adb', 'fastboot']),
            'os_version' => $faker->randomElement(['13', '12', '11', '10']),
            'sdk_version' => $faker->randomElement(['33', '32', '31', '30']),
            'authorized' => true,
            'online' => true,
            'properties' => json_encode([
                'ro.product.model' => $faker->randomElement(['Pixel 7', 'Pixel 6']),
                'ro.build.version.release' => $faker->randomElement(['13', '12']),
            ]),
        ];
    }
    
    /**
     * Create model instance
     */
    protected function createModel(array $attributes): mixed {
        $device = new Device($attributes);
        $device->save();
        return $device;
    }
    
    /**
     * Make model instance without persisting
     */
    protected function makeModel(array $attributes): mixed {
        return new Device($attributes);
    }
    
    /**
     * Set unauthorized state
     */
    public function unauthorized(): static {
        return $this->state(['authorized' => false, 'state' => 'unauthorized']);
    }
    
    /**
     * Set offline state
     */
    public function offline(): static {
        return $this->state(['online' => false, 'state' => 'offline']);
    }
    
    /**
     * Set fastboot type
     */
    public function fastboot(): static {
        return $this->state(['type' => 'fastboot']);
    }
}
