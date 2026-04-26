<?php

declare(strict_types=1);

namespace GSMSDK\Database\Factories\Firmware;

use GSMSDK\Database\Factory;
use GSMSDK\Models\Firmware;

/**
 * Firmware Factory
 */
class FirmwareFactory extends Factory {
    private array $brands = [
        'xiaomi', 'google', 'samsung', 'asus', 'motorola',
        'lg', 'nokia', 'sony', 'huawei', 'oneplus',
        'oppo', 'vivo'
    ];
    
    private array $models = [
        'xiaomi' => ['Redmi Note 12', 'Xiaomi 13', 'Poco F5', 'Redmi 12C'],
        'google' => ['Pixel 7', 'Pixel 7 Pro', 'Pixel 6a'],
        'samsung' => ['Galaxy S23', 'Galaxy S23+', 'Galaxy A54'],
        'asus' => ['Zenfone 10', 'ROG Phone 7'],
        'motorola' => ['Edge 40', 'Razr 40', 'Moto G84'],
        'lg' => ['Wing', 'Velvet'],
        'nokia' => ['G50', 'X30'],
        'sony' => ['Xperia 1 V', 'Xperia 10 V'],
        'huawei' => ['P60', 'Mate 50'],
        'oneplus' => ['11', '11R', 'Nord 3'],
        'oppo' => ['Find X6', 'Reno 10'],
        'vivo' => ['X90', 'X90 Pro']
    ];
    
    private array $regions = ['WW', 'CN', 'EU', 'IN', 'US', 'KR', 'TW', null];
    private array $versions = ['13.0', '13.1', '14.0', '14.1', '15.0'];
    private array $types = ['official', 'official', 'official', 'beta', 'stock'];
    private array $status = ['active', 'active', 'active', 'beta', 'deprecated'];
    
    /**
     * Define default attributes
     */
    protected function definition(): array {
        $brand = $this->faker()->randomElement($this->brands);
        $model = $this->faker()->randomElement($this->models[$brand] ?? ['Generic Model']);
        $version = $this->faker()->randomElement($this->versions);
        $type = $this->faker()->randomElement($this->types);
        $region = $this->faker()->randomElement($this->regions);
        
        return [
            'brand' => $brand,
            'model' => $model,
            'region' => $region,
            'version' => $version,
            'build_number' => strtoupper($this->faker()->bothify('##??##')),
            'firmware_type' => $type,
            'file_name' => $this->generateFileName($brand, $model, $version, $region),
            'file_size' => $this->generateFileSize(),
            'file_hash' => $this->faker()->sha256(),
            'download_url' => 'https://firmware.example.com/' . $brand . '/' . $model . '/' . $this->generateFileName($brand, $model, $version, $region),
            'changelog' => $this->generateChangelog(),
            'status' => $this->faker()->randomElement($this->status),
            'download_count' => $this->faker()->numberBetween(0, 10000),
            'rating' => $this->faker()->numberBetween(0, 5),
            'is_popular' => $this->faker()->boolean(20),
            'is_recommended' => $this->faker()->boolean(10)
        ];
    }
    
    /**
     * Generate file name
     */
    private function generateFileName(string $brand, string $model, string $version, ?string $region): string {
        $regionStr = $region ? '_' . $region : '';
        $extensions = ['.zip', '.tar.md5', '.tgz', '.rar', '.7z'];
        $ext = $brand === 'samsung' ? '.tar.md5' : $this->faker()->randomElement($extensions);
        
        return strtoupper($brand) . '_' . str_replace(' ', '_', $model) . '_' . $version . $regionStr . '_FIRMWARE' . $ext;
    }
    
    /**
     * Generate file size
     */
    private function generateFileSize(): string {
        $sizes = ['512MB', '1GB', '1.2GB', '1.5GB', '2GB', '2.5GB', '3GB'];
        return $this->faker()->randomElement($sizes);
    }
    
    /**
     * Generate changelog
     */
    private function generateChangelog(): string {
        $changes = [
            'Security patch update',
            'Bug fixes and stability improvements',
            'Performance optimization',
            'New features added',
            'Camera improvements',
            'Battery optimization',
            'UI enhancements',
            'Network stability fixes',
            'Audio improvements',
            'Display optimizations'
        ];
        
        $count = $this->faker()->numberBetween(3, 6);
        $selected = $this->faker()->randomElements($changes, $count);
        
        return implode("\n\n", array_map(function ($change) {
            return '• ' . $change;
        }, $selected));
    }
    
    /**
     * Create model instance
     */
    protected function createModel(array $attributes): mixed {
        $firmware = new Firmware($attributes);
        $firmware->save();
        return $firmware;
    }
    
    /**
     * Make model instance without persisting
     */
    protected function makeModel(array $attributes): mixed {
        return new Firmware($attributes);
    }
    
    /**
     * Set as popular
     */
    public function popular(): static {
        return $this->state(['is_popular' => true, 'download_count' => $this->faker()->numberBetween(5000, 50000)]);
    }
    
    /**
     * Set as recommended
     */
    public function recommended(): static {
        return $this->state(['is_recommended' => true]);
    }
    
    /**
     * Set as beta
     */
    public function beta(): static {
        return $this->state(['firmware_type' => 'beta', 'status' => 'beta']);
    }
    
    /**
     * Set as official
     */
    public function official(): static {
        return $this->state(['firmware_type' => 'official', 'status' => 'active']);
    }
}
