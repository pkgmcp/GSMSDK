<?php

declare(strict_types=1);

use GSMSDK\Database\Migration;
use GSMSDK\Database\Schema;

/**
 * User Roles and Permissions Migration
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
        // Roles table
        $sql = $this->create('roles', function (Schema $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['slug', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Role User pivot table
        $sql = $this->create('role_user', function (Schema $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
            
            $table->index(['user_id', 'role_id']);
        });
        
        $this->getPdo()->exec($sql);
        
        // User Profile table
        $sql = $this->create('user_profiles', function (Schema $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('language')->default('en');
            $table->text('address')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
            
            $table->index(['phone']);
        });
        
        $this->getPdo()->exec($sql);
        
        // File Repository table
        $sql = $this->create('file_repository', function (Schema $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('file_size');
            $table->string('file_hash', 64);
            $table->string('storage_path');
            $table->string('storage_type')->default('local');
            $table->string('visibility')->default('private');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'category', 'status']);
            $table->index(['storage_path']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Download History table
        $sql = $this->create('download_history', function (Schema $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->string('filename');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['file_id', 'created_at']);
        });
        
        $this->getPdo()->exec($sql);
        
        // API Providers table
        $sql = $this->create('api_providers', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('provider_key');
            $table->string('endpoint_url');
            $table->string('api_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->json('credentials')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit')->default(100);
            $table->integer('timeout')->default(30);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['provider_key', 'is_active']);
            $table->index(['api_key']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Email Templates table
        $sql = $this->create('email_templates', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->text('template_html');
            $table->text('template_text')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['slug', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Email Config table
        $sql = $this->create('email_config', function (Schema $table) {
            $table->id();
            $table->string('driver')->default('smtp');
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('encryption')->nullable();
            $table->string('from_address');
            $table->string('from_name');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index(['driver', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // CMS Pages table
        $sql = $this->create('cms_pages', function (Schema $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('template')->default('default');
            $table->text('content')->nullable();
            $table->json('meta_tags')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['slug', 'status']);
            $table->index(['author_id']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Themes table
        $sql = $this->create('themes', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('preview_images')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['slug', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Navigation Menus table
        $sql = $this->create('navigation_menus', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->json('items')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['location', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Telegram Bots table
        $sql = $this->create('telegram_bots', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('bot_token');
            $table->string('bot_username')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->json('webhook_config')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['bot_token', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // AI Chatbot Config table
        $sql = $this->create('ai_chatbot_config', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->default('openai');
            $table->string('api_key')->nullable();
            $table->string('model')->default('gpt-3.5-turbo');
            $table->float('temperature')->default(0.7);
            $table->integer('max_tokens')->default(1500);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->json('prompts')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['provider', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Cloud Config table
        $sql = $this->create('cloud_config', function (Schema $table) {
            $table->id();
            $table->string('provider');
            $table->string('service');
            $table->json('credentials')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['provider', 'service', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Phone Work History table
        $sql = $this->create('phone_work_history', function (Schema $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('phone_number');
            $table->string('work_type');
            $table->string('status');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'work_type']);
            $table->index(['phone_number', 'created_at']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Billing Options table
        $sql = $this->create('billing_options', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('gateway');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('interval')->default('month');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['gateway', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // File Request table
        $sql = $this->create('file_requests', function (Schema $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('filename');
            $table->string('description')->nullable();
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Hero Section Config table
        $sql = $this->create('hero_sections', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('page');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('background_image')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['page', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Features Section Config table
        $sql = $this->create('features_sections', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('page');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['page', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Footer Config table
        $sql = $this->create('footer_config', function (Schema $table) {
            $table->id();
            $table->json('links')->nullable();
            $table->json('social_media')->nullable();
            $table->text('copyright_text')->nullable();
            $table->json('contact_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // AI Assistance Config table
        $sql = $this->create('ai_assistance_config', function (Schema $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('chatbot');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->json('knowledge_base')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'is_active']);
        });
        
        $this->getPdo()->exec($sql);
        
        // Site Settings table
        $sql = $this->create('site_settings', function (Schema $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['key']);
        });
        
        $this->getPdo()->exec($sql);
    }
    
    /**
     * Rollback the migration
     */
    public function down(): void {
        $tables = [
            'site_settings',
            'ai_assistance_config',
            'footer_config',
            'features_sections',
            'hero_sections',
            'file_requests',
            'billing_options',
            'phone_work_history',
            'cloud_config',
            'ai_chatbot_config',
            'telegram_bots',
            'navigation_menus',
            'themes',
            'cms_pages',
            'email_config',
            'email_templates',
            'api_providers',
            'download_history',
            'file_repository',
            'user_profiles',
            'role_user',
            'roles'
        ];
        
        foreach ($tables as $table) {
            $sql = $this->drop($table);
            $this->getPdo()->exec($sql);
        }
    }
};
