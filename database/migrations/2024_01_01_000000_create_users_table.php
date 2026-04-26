<?php

declare(strict_types=1);

use GSMSDK\Database\Migration;
use GSMSDK\Database\Schema;

/**
 * Create Users Table Migration
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
        $sql = $this->create('users', function (Schema $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('api_token', 80)->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        $this->getPdo()->exec($sql);
    }
    
    /**
     * Rollback the migration
     */
    public function down(): void {
        $sql = $this->drop('users');
        $this->getPdo()->exec($sql);
    }
};
