<?php

declare(strict_types=1);

use GSMSDK\Database\Migration;
use GSMSDK\Database\Schema;

/**
 * Create Flash Logs Table Migration
 */
return new class extends Migration {
    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        parent::__construct(new \GSMSDK\Database\Connection($config));
    }
    
    /**
     * Run the migration
     */
    public function up(): void {
        $sql = $this->create('flash_logs', function (Schema $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('user_id');
            $table->string('operation', 50); // flash, erase, reboot, etc.
            $table->string('partition', 50)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->json('options')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed']);
            $table->text('output')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->timestamps();
            
            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        $this->getPdo()->exec($sql);
    }
    
    /**
     * Rollback the migration
     */
    public function down(): void {
        $sql = $this->drop('flash_logs');
        $this->getPdo()->exec($sql);
    }
};
