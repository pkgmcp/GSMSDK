<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit;

use GSMSDK\Tests\Pest;
use GSMSDK\Models\User;
use GSMSDK\Models\Role;
use GSMSDK\Models\UserProfile;
use GSMSDK\Models\FileRepository;
use GSMSDK\Models\DownloadHistory;
use GSMSDK\Models\Firmware;
use GSMSDK\Models\ApiProvider;
use GSMSDK\Models\EmailConfig;
use GSMSDK\Models\EmailTemplate;
use GSMSDK\Models\CmsPage;
use GSMSDK\Models\Theme;
use GSMSDK\Models\NavigationMenu;
use GSMSDK\Models\TelegramBot;
use GSMSDK\Models\AiChatbotConfig;
use GSMSDK\Models\CloudConfig;
use GSMSDK\Models\PhoneWorkHistory;
use GSMSDK\Models\BillingOption;
use GSMSDK\Models\FileRequest;
use GSMSDK\Models\HeroSection;
use GSMSDK\Models\FeaturesSection;
use GSMSDK\Models\FooterConfig;
use GSMSDK\Models\AiAssistanceConfig;
use GSMSDK\Models\SiteSetting;

/**
 * Comprehensive Feature Test Suite
 * Tests all v3.0.0 features
 */
class ComprehensiveFeatureTest extends Pest {
    
    /**
     * Test User Management Features
     */
    public function test_user_management_features(): void {
        // Test Role Creation
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role for unit testing',
            'permissions' => json_encode(['test.permission']),
            'is_active' => true
        ]);
        
        expect($role->id)->toBeGreaterThan(0);
        expect($role->slug)->toBe('test-role');
        expect($role->is_active)->toBeTrue();
        
        // Test User Creation
        $user = User::create([
            'username' => 'testuser_' . time(),
            'email' => 'test' . time() . '@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        
        expect($user->id)->toBeGreaterThan(0);
        expect($user->email)->toContain('@example.com');
        expect($user->status)->toBe('active');
        
        // Test User Profile Association
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'phone' => '+1-555-TEST-' . time(),
            'avatar' => 'https://ui-avatars.com/api/?name=Test',
            'timezone' => 'UTC',
            'language' => 'en',
            'address' => '123 Test Street',
            'preferences' => json_encode(['test' => true])
        ]);
        
        expect($profile->id)->toBeGreaterThan(0);
        expect($profile->user_id)->toBe($user->id);
        expect($profile->timezone)->toBe('UTC');
        
        // Test JSON field parsing
        $prefs = json_decode($profile->preferences, true);
        expect($prefs['test'])->toBeTrue();
        
        $this->cleanupModels([$role, $user, $profile]);
    }
    
    /**
     * Test File Repository Features
     */
    public function test_file_repository_features(): void {
        // Create test user
        $user = User::create([
            'username' => 'fileuser_' . time(),
            'email' => 'file' . time() . '@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        
        // Test File Upload Record
        $file = FileRepository::create([
            'user_id' => $user->id,
            'filename' => 'test_file_' . time() . '.zip',
            'original_name' => 'test_package.zip',
            'mime_type' => 'application/zip',
            'file_size' => '1048576 bytes',
            'file_hash' => hash('sha256', 'test_file_' . time()),
            'storage_path' => '/uploads/test/file.zip',
            'storage_type' => 'local',
            'visibility' => 'private',
            'description' => 'Test file upload',
            'category' => 'firmware',
            'status' => 'active',
            'download_count' => 0
        ]);
        
        expect($file->id)->toBeGreaterThan(0);
        expect($file->filename)->toContain('.zip');
        expect($file->storage_type)->toBe('local');
        expect($file->visibility)->toBe('private');
        
        // Test file hash is SHA-256 (64 chars)
        expect(strlen($file->file_hash))->toBe(64);
        
        // Test download count increment
        $file->incrementDownload();
        expect($file->download_count)->toBe(1);
        
        // Test metadata JSON
        $file->metadata = json_encode(['version' => '1.0', 'test' => true]);
        $file->save();
        
        $meta = json_decode($file->metadata, true);
        expect($meta['version'])->toBe('1.0');
        expect($meta['test'])->toBeTrue();
        
        $this->cleanupModels([$user, $file]);
    }
    
    /**
     * Test Download History Features
     */
    public function test_download_history_features(): void {
        // Create test user
        $user = User::create([
            'username' => 'historyuser_' . time(),
            'email' => 'history' . time() . '@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        
        // Test Download History Record
        $history = DownloadHistory::create([
            'user_id' => $user->id,
            'file_id' => 1001,
            'filename' => 'test_firmware.zip',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'device_type' => 'desktop',
            'country' => 'US',
            'city' => 'New York',
            'metadata' => json_encode(['browser' => 'Chrome', 'os' => 'Windows'])
        ]);
        
        expect($history->id)->toBeGreaterThan(0);
        expect($history->ip_address)->toBe('192.168.1.100');
        expect($history->device_type)->toBe('desktop');
        expect($history->country)->toBe('US');
        expect($history->city)->toBe('New York');
        
        // Test metadata JSON parsing
        $meta = json_decode($history->metadata, true);
        expect($meta['browser'])->toBe('Chrome');
        expect($meta['os'])->toBe('Windows');
        
        // Test valid device types
        $validTypes = ['desktop', 'mobile', 'tablet'];
        expect(in_array($history->device_type, $validTypes))->toBeTrue();
        
        $this->cleanupModels([$user, $history]);
    }
    
    /**
     * Test Phone Work History Features
     */
    public function test_phone_work_history_features(): void {
        // Create test user
        $user = User::create([
            'username' => 'workuser_' . time(),
            'email' => 'work' . time() . '@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        
        // Test Phone Work History Record
        $work = PhoneWorkHistory::create([
            'user_id' => $user->id,
            'phone_number' => '+1-555-TEST-001',
            'work_type' => 'imei_repair',
            'status' => 'completed',
            'description' => 'IMEI repair via flash mode',
            'metadata' => json_encode([
                'method' => 'flash_mode',
                'device_model' => 'test_device'
            ])
        ]);
        
        expect($work->id)->toBeGreaterThan(0);
        expect($work->phone_number)->toBe('+1-555-TEST-001');
        expect($work->work_type)->toBe('imei_repair');
        expect($work->status)->toBe('completed');
        
        // Test valid work types
        $validTypes = ['imei_repair', 'frp_remove', 'firmware_update', 'device_unlock', 'data_recovery'];
        expect(in_array($work->work_type, $validTypes))->toBeTrue();
        
        // Test valid statuses
        $validStatuses = ['pending', 'in_progress', 'completed', 'failed', 'cancelled'];
        expect(in_array($work->status, $validStatuses))->toBeTrue();
        
        // Test metadata JSON
        $meta = json_decode($work->metadata, true);
        expect($meta['method'])->toBe('flash_mode');
        expect($meta['device_model'])->toBe('test_device');
        
        $this->cleanupModels([$user, $work]);
    }
    
    /**
     * Test API Provider Features
     */
    public function test_api_provider_features(): void {
        // Test API Provider Record
        $provider = ApiProvider::create([
            'name' => 'Test AI Provider',
            'provider_key' => 'test_provider',
            'endpoint_url' => 'https://api.testprovider.com/v1',
            'api_key' => 'test_key_' . bin2hex(random_bytes(16)),
            'credentials' => json_encode(['project_id' => 'test_project']),
            'is_active' => true,
            'rate_limit' => 1000,
            'timeout' => 60
        ]);
        
        expect($provider->id)->toBeGreaterThan(0);
        expect($provider->provider_key)->toBe('test_provider');
        expect($provider->is_active)->toBeTrue();
        expect($provider->rate_limit)->toBe(1000);
        expect($provider->timeout)->toBe(60);
        
        // Test credentials JSON
        $creds = json_decode($provider->credentials, true);
        expect($creds['project_id'])->toBe('test_project');
        
        // Test endpoint URL format
        expect(filter_var($provider->endpoint_url, FILTER_VALIDATE_URL))->not->toBeFalse();
        
        $this->cleanupModels([$provider]);
    }
    
    /**
     * Test Email System Features
     */
    public function test_email_system_features(): void {
        // Test Email Config
        $config = EmailConfig::create([
            'driver' => 'smtp',
            'host' => 'smtp.test.com',
            'port' => 587,
            'username' => 'test@test.com',
            'password' => 'encrypted_password',
            'encryption' => 'tls',
            'from_address' => 'noreply@test.com',
            'from_name' => 'Test Service',
            'is_active' => true,
            'settings' => json_encode(['timeout' => 30, 'smtp_auth' => true])
        ]);
        
        expect($config->id)->toBeGreaterThan(0);
        expect($config->driver)->toBe('smtp');
        expect($config->port)->toBe(587);
        expect($config->is_active)->toBeTrue();
        
        // Test valid drivers
        $validDrivers = ['smtp', 'sendmail', 'mail', 'ses', 'mailgun'];
        expect(in_array($config->driver, $validDrivers))->toBeTrue();
        
        // Test valid encryptions
        $validEncryptions = ['tls', 'ssl', ''];
        expect(in_array($config->encryption, $validEncryptions))->toBeTrue();
        
        // Test Settings JSON
        $settings = json_decode($config->settings, true);
        expect($settings['timeout'])->toBe(30);
        expect($settings['smtp_auth'])->toBeTrue();
        
        // Test Email Template
        $template = EmailTemplate::create([
            'name' => 'Test Template',
            'slug' => 'test-template',
            'subject' => 'Test Email {{variable}}',
            'template_html' => '<html><body>Test {{content}}</body></html>',
            'variables' => json_encode(['variable', 'content']),
            'is_active' => true
        ]);
        
        expect($template->id)->toBeGreaterThan(0);
        expect($template->slug)->toBe('test-template');
        expect($template->is_active)->toBeTrue();
        expect($template->subject)->toContain('{{variable}}');
        
        // Test variables JSON
        $vars = json_decode($template->variables, true);
        expect(in_array('variable', $vars))->toBeTrue();
        expect(in_array('content', $vars))->toBeTrue();
        
        $this->cleanupModels([$config, $template]);
    }
    
    /**
     * Test CMS Features
     */
    public function test_cms_features(): void {
        // Create test user for author
        $user = User::create([
            'username' => 'cmsuser_' . time(),
            'email' => 'cms' . time() . '@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        
        // Test CMS Page
        $page = CmsPage::create([
            'title' => 'Test Page',
            'slug' => 'test-page-' . time(),
            'template' => 'default',
            'content' => '<h1>Test Page</h1><p>This is a test page content.</p>',
            'meta_tags' => json_encode([
                'title' => 'Test Page - Test Site',
                'description' => 'This is a test page',
                'keywords' => 'test, page, cms'
            ]),
            'status' => 'draft',
            'author_id' => $user->id
        ]);
        
        expect($page->id)->toBeGreaterThan(0);
        expect($page->slug)->toContain('test-page-');
        expect($page->template)->toBe('default');
        expect($page->status)->toBe('draft');
        expect($page->author_id)->toBe($user->id);
        
        // Test valid statuses
        $validStatuses = ['draft', 'published', 'archived'];
        expect(in_array($page->status, $validStatuses))->toBeTrue();
        
        // Test Meta Tags JSON
        $meta = json_decode($page->meta_tags, true);
        expect($meta['title'])->toBe('Test Page - Test Site');
        expect($meta['description'])->toBe('This is a test page');
        expect($meta['keywords'])->toBe('test, page, cms');
        
        // Test published_at is null for draft
        expect($page->published_at)->toBeNull();
        
        $this->cleanupModels([$user, $page]);
    }
    
    /**
     * Test Theme Management Features
     */
    public function test_theme_management_features(): void {
        // Test Theme
        $theme = Theme::create([
            'name' => 'Test Theme',
            'slug' => 'test-theme',
            'version' => '1.0.0',
            'author' => 'Test Author',
            'description' => 'A test theme for unit testing',
            'settings' => json_encode([
                'primary_color' => '#ff0000',
                'secondary_color' => '#00ff00',
                'background_color' => '#000000'
            ]),
            'is_active' => false,
            'preview_images' => json_encode(['/themes/test/preview-1.png'])
        ]);
        
        expect($theme->id)->toBeGreaterThan(0);
        expect($theme->slug)->toBe('test-theme');
        expect($theme->version)->toBe('1.0.0');
        expect($theme->is_active)->toBeFalse();
        
        // Test Settings JSON
        $settings = json_decode($theme->settings, true);
        expect($settings['primary_color'])->toBe('#ff0000');
        expect($settings['secondary_color'])->toBe('#00ff00');
        expect($settings['background_color'])->toBe('#000000');
        
        // Test Preview Images JSON
        $previews = json_decode($theme->preview_images, true);
        expect(count($previews))->toBe(1);
        expect($previews[0])->toContain('.png');
        
        // Test only one theme can be active
        $theme2 = Theme::create([
            'name' => 'Active Theme',
            'slug' => 'active-theme',
            'version' => '1.0.0',
            'author' => 'Test Author',
            'description' => 'Should be active',
            'settings' => json_encode([]),
            'is_active' => true,
            'preview_images' => json_encode([])
        ]);
        
        expect($theme2->is_active)->toBeTrue();
        
        $this->cleanupModels([$theme, $theme2]);
    }
    
    /**
     * Test Navigation Menu Features
     */
    public function test_navigation_menu_features(): void {
        // Test Navigation Menu
        $menu = NavigationMenu::create([
            'name' => 'Test Menu',
            'location' => 'header',
            'is_active' => true,
            'sort_order' => 1,
            'items' => json_encode([
                ['title' => 'Home', 'url' => '/', 'target' => '_self', 'order' => 1],
                ['title' => 'About', 'url' => '/about', 'target' => '_self', 'order' => 2]
            ])
        ]);
        
        expect($menu->id)->toBeGreaterThan(0);
        expect($menu->location)->toBe('header');
        expect($menu->is_active)->toBeTrue();
        expect($menu->sort_order)->toBe(1);
        
        // Test valid locations
        $validLocations = ['header', 'footer', 'sidebar', 'mobile_menu'];
        expect(in_array($menu->location, $validLocations))->toBeTrue();
        
        // Test Items JSON
        $items = json_decode($menu->items, true);
        expect(count($items))->toBe(2);
        expect($items[0]['title'])->toBe('Home');
        expect($items[0]['url'])->toBe('/');
        expect($items[1]['title'])->toBe('About');
        
        $this->cleanupModels([$menu]);
    }
    
    /**
     * Test Telegram Bot Features
     */
    public function test_telegram_bot_features(): void {
        // Test Telegram Bot
        $bot = TelegramBot::create([
            'name' => 'Test Bot',
            'bot_token' => '1234567890:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnop',
            'bot_username' => 'TestBot',
            'is_active' => true,
            'settings' => json_encode([
                'webhook_url' => 'https://test.com/webhook',
                'allowed_chats' => [123456],
                'commands' => ['/start', '/help']
            ]),
            'webhook_config' => json_encode([
                'url' => 'https://test.com/webhook',
                'secret_token' => bin2hex(random_bytes(32))
            ])
        ]);
        
        expect($bot->id)->toBeGreaterThan(0);
        expect($bot->bot_username)->toBe('TestBot');
        expect($bot->is_active)->toBeTrue();
        
        // Test bot token format (basic check)
        expect(strlen($bot->bot_token))->toBeGreaterThan(10);
        
        // Test Settings JSON
        $settings = json_decode($bot->settings, true);
        expect($settings['webhook_url'])->toBe('https://test.com/webhook');
        expect($settings['commands'][0])->toBe('/start');
        expect($settings['commands'][1])->toBe('/help');
        
        // Test Webhook Config JSON
        $webhook = json_decode($bot->webhook_config, true);
        expect($webhook['url'])->toBe('https://test.com/webhook');
        expect(strlen($webhook['secret_token']))->toBe(64); // 32 bytes hex encoded
        
        $this->cleanupModels([$bot]);
    }
    
    /**
     * Test AI Chatbot Features
     */
    public function test_ai_chatbot_features(): void {
        // Test AI Chatbot Config
        $chatbot = AiChatbotConfig::create([
            'name' => 'Test AI',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'temperature' => 0.7,
            'max_tokens' => 1500,
            'is_active' => true,
            'settings' => json_encode([
                'api_key' => 'sk-test-key',
                'streaming' => true,
                'cache_responses' => true
            ]),
            'prompts' => json_encode([
                'system' => 'You are a helpful assistant',
                'welcome' => 'Hello! How can I help?'
            ]),
            'knowledge_base' => json_encode([
                'firmware_guides' => true,
                'device_compatibility' => true
            ])
        ]);
        
        expect($chatbot->id)->toBeGreaterThan(0);
        expect($chatbot->provider)->toBe('openai');
        expect($chatbot->model)->toBe('gpt-4');
        expect($chatbot->temperature)->toBe(0.7);
        expect($chatbot->max_tokens)->toBe(1500);
        expect($chatbot->is_active)->toBeTrue();
        
        // Test Settings JSON
        $settings = json_decode($chatbot->settings, true);
        expect($settings['api_key'])->toBe('sk-test-key');
        expect($settings['streaming'])->toBeTrue();
        expect($settings['cache_responses'])->toBeTrue();
        
        // Test Prompts JSON
        $prompts = json_decode($chatbot->prompts, true);
        expect($prompts['system'])->toBe('You are a helpful assistant');
        expect($prompts['welcome'])->toBe('Hello! How can I help?');
        
        // Test Knowledge Base JSON
        $kb = json_decode($chatbot->knowledge_base, true);
        expect($kb['firmware_guides'])->toBeTrue();
        expect($kb['device_compatibility'])->toBeTrue();
        
        $this->cleanupModels([$chatbot]);
    }
    
    /**
     * Test Cloud Config Features
     */
    public function test_cloud_config_features(): void {
        // Test Cloud Config
        $cloud = CloudConfig::create([
            'provider' => 'google',
            'service' => 'cloud-storage',
            'credentials' => json_encode([
                'project_id' => 'test-project',
                'key_file' => '/path/to/key.json'
            ]),
            'settings' => json_encode([
                'bucket' => 'test-bucket',
                'region' => 'us-central1'
            ]),
            'is_active' => true
        ]);
        
        expect($cloud->id)->toBeGreaterThan(0);
        expect($cloud->provider)->toBe('google');
        expect($cloud->service)->toBe('cloud-storage');
        expect($cloud->is_active)->toBeTrue();
        
        // Test valid providers
        $validProviders = ['google', 'aws', 'azure', 'cloudflare', 'digitalocean'];
        expect(in_array($cloud->provider, $validProviders))->toBeTrue();
        
        // Test Credentials JSON
        $creds = json_decode($cloud->credentials, true);
        expect($creds['project_id'])->toBe('test-project');
        expect($creds['key_file'])->toBe('/path/to/key.json');
        
        // Test Settings JSON
        $settings = json_decode($cloud->settings, true);
        expect($settings['bucket'])->toBe('test-bucket');
        expect($settings['region'])->toBe('us-central1');
        
        $this->cleanupModels([$cloud]);
    }
    
    /**
     * Test Billing System Features
     */
    public function test_billing_features(): void {
        // Test Billing Option
        $billing = BillingOption::create([
            'name' => 'Pro Plan',
            'gateway' => 'stripe',
            'amount' => 29.99,
            'currency' => 'USD',
            'interval' => 'month',
            'features' => json_encode([
                'unlimited_downloads',
                'priority_support',
                'imei_repair'
            ]),
            'is_active' => true,
            'sort_order' => 1
        ]);
        
        expect($billing->id)->toBeGreaterThan(0);
        expect($billing->name)->toBe('Pro Plan');
        expect($billing->gateway)->toBe('stripe');
        expect($billing->amount)->toBe(29.99);
        expect($billing->currency)->toBe('USD');
        expect($billing->interval)->toBe('month');
        expect($billing->is_active)->toBeTrue();
        
        // Test valid gateways
        $validGateways = ['stripe', 'paypal', 'razorpay', 'mollie', 'square', 'braintree'];
        expect(in_array($billing->gateway, $validGateways))->toBeTrue();
        
        // Test valid intervals
        $validIntervals = ['month', 'year', 'week', 'day', 'one_time'];
        expect(in_array($billing->interval, $validIntervals))->toBeTrue();
        
        // Test Features JSON
        $features = json_decode($billing->features, true);
        expect(in_array('unlimited_downloads', $features))->toBeTrue();
        expect(in_array('priority_support', $features))->toBeTrue();
        expect(in_array('imei_repair', $features))->toBeTrue();
        
        $this->cleanupModels([$billing]);
    }
    
    /**
     * Test File Request Features
     */
    public function test_file_request_features(): void {
        // Create test user
        $user = User::create([
            'username' => 'requestuser_' . time(),
            'email' => 'request' . time() . '@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        
        // Test File Request
        $request = FileRequest::create([
            'user_id' => $user->id,
            'filename' => 'custom_firmware.zip',
            'description' => 'Need firmware for custom device',
            'status' => 'pending',
            'metadata' => json_encode([
                'priority' => 'high',
                'device_model' => 'custom_device'
            ])
        ]);
        
        expect($request->id)->toBeGreaterThan(0);
        expect($request->user_id)->toBe($user->id);
        expect($request->filename)->toBe('custom_firmware.zip');
        expect($request->status)->toBe('pending');
        
        // Test valid statuses
        $validStatuses = ['pending', 'approved', 'rejected', 'processing', 'completed'];
        expect(in_array($request->status, $validStatuses))->toBeTrue();
        
        // Test Metadata JSON
        $meta = json_decode($request->metadata, true);
        expect($meta['priority'])->toBe('high');
        expect($meta['device_model'])->toBe('custom_device');
        
        $this->cleanupModels([$user, $request]);
    }
    
    /**
     * Test Hero Section Features
     */
    public function test_hero_section_features(): void {
        // Test Hero Section
        $hero = HeroSection::create([
            'name' => 'Main Hero',
            'page' => 'home',
            'title' => 'Your Complete Firmware Solution',
            'subtitle' => 'Download firmware for 150+ devices',
            'background_image' => '/images/hero-bg.jpg',
            'button_text' => 'Explore Firmware',
            'button_url' => '/firmware',
            'is_active' => true,
            'sort_order' => 1
        ]);
        
        expect($hero->id)->toBeGreaterThan(0);
        expect($hero->page)->toBe('home');
        expect($hero->title)->toBe('Your Complete Firmware Solution');
        expect($hero->is_active)->toBeTrue();
        expect($hero->sort_order)->toBe(1);
        
        $this->cleanupModels([$hero]);
    }
    
    /**
     * Test Features Section
     */
    public function test_features_section_features(): void {
        // Test Features Section
        $features = FeaturesSection::create([
            'name' => 'Our Features',
            'page' => 'home',
            'features' => json_encode([
                ['icon' => 'shield', 'title' => 'IMEI Repair', 'description' => 'Fix IMEI issues'],
                ['icon' => 'lock', 'title' => 'FRP Remove', 'description' => 'Bypass FRP']
            ]),
            'is_active' => true,
            'sort_order' => 1
        ]);
        
        expect($features->id)->toBeGreaterThan(0);
        expect($features->page)->toBe('home');
        expect($features->is_active)->toBeTrue();
        
        // Test Features JSON
        $feat = json_decode($features->features, true);
        expect(count($feat))->toBe(2);
        expect($feat[0]['icon'])->toBe('shield');
        expect($feat[0]['title'])->toBe('IMEI Repair');
        expect($feat[1]['icon'])->toBe('lock');
        
        $this->cleanupModels([$features]);
    }
    
    /**
     * Test Footer Config Features
     */
    public function test_footer_config_features(): void {
        // Test Footer Config
        $footer = FooterConfig::create([
            'links' => json_encode([
                ['title' => 'About', 'url' => '/about'],
                ['title' => 'Contact', 'url' => '/contact']
            ]),
            'social_media' => json_encode([
                ['platform' => 'telegram', 'url' => 'https://t.me/test'],
                ['platform' => 'github', 'url' => 'https://github.com/test']
            ]),
            'copyright_text' => '© 2024 Test Site',
            'contact_info' => json_encode([
                'email' => 'contact@test.com',
                'phone' => '+1-555-TEST'
            ]),
            'is_active' => true
        ]);
        
        expect($footer->id)->toBeGreaterThan(0);
        expect($footer->is_active)->toBeTrue();
        
        // Test Links JSON
        $links = json_decode($footer->links, true);
        expect(count($links))->toBe(2);
        expect($links[0]['title'])->toBe('About');
        
        // Test Social Media JSON
        $social = json_decode($footer->social_media, true);
        expect(count($social)).toBe(2);
        expect($social[0]['platform'])->toBe('telegram');
        
        // Test Contact Info JSON
        $contact = json_decode($footer->contact_info, true);
        expect($contact['email'])->toBe('contact@test.com');
        expect($contact['phone'])->toBe('+1-555-TEST');
        
        $this->cleanupModels([$footer]);
    }
    
    /**
     * Test AI Assistance Features
     */
    public function test_ai_assistance_features(): void {
        // Test AI Assistance Config
        $assistance = AiAssistanceConfig::create([
            'name' => 'Help Desk AI',
            'type' => 'chatbot',
            'is_active' => true,
            'settings' => json_encode([
                'provider' => 'openai',
                'model' => 'gpt-4',
                'streaming' => true
            ]),
            'knowledge_base' => json_encode([
                'firmware_guides' => true,
                'repair_procedures' => true,
                'troubleshooting' => true
            ])
        ]);
        
        expect($assistance->id)->toBeGreaterThan(0);
        expect($assistance->type)->toBe('chatbot');
        expect($assistance->is_active)->toBeTrue();
        
        // Test valid types
        $validTypes = ['chatbot', 'virtual_assistant', 'faq_bot', 'support_agent'];
        expect(in_array($assistance->type, $validTypes))->toBeTrue();
        
        // Test Settings JSON
        $settings = json_decode($assistance->settings, true);
        expect($settings['provider'])->toBe('openai');
        expect($settings['model'])->toBe('gpt-4');
        expect($settings['streaming'])->toBeTrue();
        
        // Test Knowledge Base JSON
        $kb = json_decode($assistance->knowledge_base, true);
        expect($kb['firmware_guides'])->toBeTrue();
        expect($kb['repair_procedures'])->toBeTrue();
        expect($kb['troubleshooting'])->toBeTrue();
        
        $this->cleanupModels([$assistance]);
    }
    
    /**
     * Test Site Settings Features
     */
    public function test_site_settings_features(): void {
        // Test Site Settings
        $settings = [
            ['key' => 'site_name', 'value' => 'Test Site', 'type' => 'string', 'description' => 'Site name'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Maintenance mode'],
            ['key' => 'download_limit', 'value' => '100', 'type' => 'integer', 'description' => 'Download limit']
        ];
        
        foreach ($settings as $setting) {
            $siteSetting = SiteSetting::create($setting);
            
            expect($siteSetting->id)->toBeGreaterThan(0);
            expect($siteSetting->key)->toBe($setting['key']);
            expect($siteSetting->type)->toBe($setting['type']);
            
            // Test that key is unique
            $duplicate = SiteSetting::create($setting);
            expect($duplicate->id)->not->toBe($siteSetting->id);
            
            $this->cleanupModels([$siteSetting, $duplicate]);
        }
    }
    
    /**
     * Test Firmware Features Integration
     */
    public function test_firmware_integration_features(): void {
        // Create test firmware
        $firmware = Firmware::create([
            'brand' => 'test',
            'model' => 'test_device',
            'version' => '1.0.0',
            'firmware_type' => 'stable',
            'file_name' => 'test_firmware.zip',
            'file_hash' => hash('sha256', 'test_firmware'),
            'download_url' => 'https://example.com/test_firmware.zip',
            'file_size' => '10485760 bytes',
            'security_patch' => '2024-01-01',
            'android_version' => '14.0',
            'status' => 'active',
            'imei_repair_supported' => true,
            'frp_remove_supported' => true,
            'ota_supported' => true,
            'region' => 'global',
            'download_count' => 0
        ]);
        
        expect($firmware->id)->toBeGreaterThan(0);
        expect($firmware->brand)->toBe('test');
        expect($firmware->imei_repair_supported)->toBeTrue();
        expect($firmware->frp_remove_supported)->toBeTrue();
        expect($firmware->ota_supported)->toBeTrue();
        
        // Test query methods
        $imeiFirmware = Firmware::imeiRepairSupported()->get();
        expect($imeiFirmware->count())->toBeGreaterThan(0);
        
        $frpFirmware = Firmware::frpRemoveSupported()->get();
        expect($frpFirmware->count())->toBeGreaterThan(0);
        
        $hyperosFirmware = Firmware::hyperos()->get();
        expect(is_array($hyperosFirmware))->toBeTrue();
        
        $brands = Firmware::getAllBrands();
        expect(is_array($brands))->toBeTrue();
        
        $models = Firmware::getModelsForBrand('test');
        expect(is_array($models))->toBeTrue();
        
        $regions = Firmware::getRegionsForDevice('test', 'test_device');
        expect(is_array($regions))->toBeTrue();
        
        $this->cleanupModels([$firmware]);
    }
    
    /**
     * Test Relationship Features
     */
    public function test_relationship_features(): void {
        // Create user
        $user = User::create([
            'username' => 'reluser_' . time(),
            'email' => 'rel' . time() . '@example.com',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        
        // Create profile (1:1 relationship)
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'phone' => '+1-555-REL-001',
            'avatar' => 'https://ui-avatars.com/api/?name=Rel',
            'timezone' => 'UTC',
            'language' => 'en',
            'address' => '456 Relation Street',
            'preferences' => json_encode(['test' => true])
        ]);
        
        // Create files (1:many relationship)
        $files = [];
        for ($i = 1; $i <= 3; $i++) {
            $file = FileRepository::create([
                'user_id' => $user->id,
                'filename' => 'rel_file_' . $i . '.zip',
                'original_name' => 'rel_package_' . $i . '.zip',
                'mime_type' => 'application/zip',
                'file_size' => (rand(1, 100) * 1048576) . ' bytes',
                'file_hash' => hash('sha256', 'rel_file_' . $i . '_' . time()),
                'storage_path' => '/uploads/rel/file_' . $i . '.zip',
                'storage_type' => 'local',
                'visibility' => 'private',
                'description' => 'Related file ' . $i,
                'category' => 'firmware',
                'status' => 'active',
                'download_count' => 0
            ]);
            $files[] = $file;
        }
        
        // Create download history (1:many relationship)
        $histories = [];
        for ($i = 1; $i <=}