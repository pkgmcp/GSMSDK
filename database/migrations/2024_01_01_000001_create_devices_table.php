<?php

declare(strict_types=1);

use GSMSDK\Database\Migration;
use GSMSDK\Database\Schema;

/**
 * Create Devices Table Migration
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
        $sql = $this->create('devices', function (Schema $table) {
            $table->id();
            $table->string('serial', 100)->unique();
            $table->string('model', 100);
            $table->string('product', 100);
            $table->string('manufacturer', 100);
            $table->string('state', 50);
            $table->string('type', 20)->default('adb'); // adb, fastboot
            $table->string('os_version', 50)->nullable();
            $table->string('sdk_version', 20)->nullable();
            $table->boolean('authorized')->default(false);
            $table->boolean('online')->default(false);
            $table->json('properties')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
        
        $this->getPdo()->exec($sql);
    }
    
    /**
     * Rollback the migration
     */
    public function down(): void {
        $sql = $this->drop('devices');
        $this->getPdo()->exec($sql);
    }
};
