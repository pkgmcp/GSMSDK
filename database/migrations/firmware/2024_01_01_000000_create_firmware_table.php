<?php

declare(strict_types=1);

use GSMSDK\Database\Migration;
use GSMSDK\Database\Schema;

/**
 * Create Firmware Table Migration
 * 
 * Enhanced with security patches, IMEI support, flash mode tracking
 */
return new class extends Migration {
    public function __construct() {
        $config = require __DIR__ . '/../../../config/database.php';
        parent::__construct(new \GSMSDK\Database\Connection($config));
    }
    
    /**
     * Run the migration
     */
    public function up(): void {
        $sql = $this->create('firmware', function (Schema $table) {
            $table->id();
            $table->string('brand', 50);
            $table->string('model', 100);
            $table->string('region', 50)->nullable();
            $table->string('version', 50);
            $table->string('build_number', 50)->nullable();
            $table->string('security_patch', 50)->nullable(); // Security patch date
            $table->string('android_version', 50)->nullable();
            $table->string('firmware_type', 50); // official, beta, custom, stock, hyperos
            $table->string('file_name', 255);
            $table->string('file_size', 50);
            $table->string('file_hash', 64); // SHA256
            $table->string('download_url', 512);
            $table->string('changelog', 5000)->nullable();
            $table->string('status', 20)->default('active'); // active, deprecated, beta
            $table->integer('download_count')->default(0);
            $table->integer('rating')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->boolean('imei_repair_supported')->default(false); // IMEI repair capability
            $table->boolean('flash_mode_supported')->default(true); // Flash mode flashing
            $table->boolean('adb_mode_supported')->default(true); // ADB mode flashing
            $table->boolean('frp_remove_supported')->default(false); // FRP removal
            $table->boolean('camera_sms_working')->default(true); // Post-repair functionality
            $table->boolean('ota_supported')->default(true); // OTA update support
            $table->boolean('factory_reset_safe')->default(true); // Factory reset safety
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['brand', 'model']);
            $table->index(['version']);
            $table->index(['status']);
            $table->index(['is_popular']);
            $table->index(['security_patch']);
            $table->index(['firmware_type']);
        });
        
        $this->getPdo()->exec($sql);
    }
    
    /**
     * Rollback the migration
     */
    public function down(): void {
        $sql = $this->drop('firmware');
        $this->getPdo()->exec($sql);
    }
};
