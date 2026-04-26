<?php

declare(strict_types=1);

namespace GSMSDK\Database\Factories;

use GSMSDK\Database\Factory;
use GSMSDK\Models\User;

/**
 * User Factory
 */
class UserFactory extends Factory {
    /**
     * Define the model's default state
     */
    protected function definition(): array {
        return [
            'name' => $this->faker()->name(),
            'email' => $this->faker()->unique()->email(),
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'api_token' => $this->faker()->unique()->sha256(),
            'active' => true,
        ];
    }
    
    /**
     * Create model instance
     */
    protected function createModel(array $attributes): mixed {
        $user = new User($attributes);
        $user->save();
        return $user;
    }
    
    /**
     * Make model instance without persisting
     */
    protected function makeModel(array $attributes): mixed {
        return new User($attributes);
    }
    
    /**
     * Set inactive state
     */
    public function inactive(): static {
        return $this->state(['active' => false]);
    }
    
    /**
     * Set admin role
     */
    public function admin(): static {
        return $this->state(['is_admin' => true]);
    }
    
    /**
     * Get faker instance
     */
    private function faker(): \Faker\Generator {
        static $faker = null;
        
        if ($faker === null) {
            $faker = \Faker\Factory::create();
        }
        
        return $faker;
    }
}
