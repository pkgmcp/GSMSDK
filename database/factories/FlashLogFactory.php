<?php

declare(strict_types=1);

namespace GSMSDK\Database\Factories;

use GSMSDK\Database\Factory;
use GSMSDK\Models\FlashLog;

/**
 * Flash Log Factory
 */
class FlashLogFactory extends Factory {
    /**
     * Define the model's default state
     */
    protected function definition(): array {
        $faker = \Faker\Factory::create();
        
        return [
            'operation' => $faker->randomElement(['flash', 'erase', 'reboot', 'boot']),
            'partition' => $faker->randomElement(['boot', 'system', 'vendor', 'product', 'recovery']),
            'image_path' => $faker->optional()->filePath(),
            'options' => json_encode([
                'verify' => $faker->boolean(),
                'slot' => $faker->randomElement(['a', 'b', null]),
            ]),
            'status' => $faker->randomElement(['completed', 'failed']),
            'output' => $faker->optional()->text(500),
            'duration' => $faker->numberBetween(1, 300),
        ];
    }
    
    /**
     * Create model instance
     */
    protected function createModel(array $attributes): mixed {
        $flashLog = new FlashLog($attributes);
        $flashLog->save();
        return $flashLog;
    }
    
    /**
     * Make model instance without persisting
     */
    protected function makeModel(array $attributes): mixed {
        return new FlashLog($attributes);
    }
    
    /**
     * Set pending status
     */
    public function pending(): static {
        return $this->state(['status' => 'pending']);
    }
    
    /**
     * Set running status
     */
    public function running(): static {
        return $this->state(['status' => 'running']);
    }
    
    /**
     * Set flash operation
     */
    public function flash(): static {
        return $this->state(['operation' => 'flash']);
    }
    
    /**
     * Set failed status
     */
    public function failed(): static {
        return $this->state(['status' => 'failed']);
    }
}
