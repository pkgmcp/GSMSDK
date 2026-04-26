<?php

declare(strict_types=1);

namespace GSMSDK\Database\Seeders;

use GSMSDK\Database\Seeder;
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
 * Comprehensive Seeder - All Dynamic Data with @foreach Loops
 */
class ComprehensiveSeeder extends Seeder {
    /**
     * Run the comprehensive seeder
     */
    public function run(): void {
        $this->info('🌱 Starting Comprehensive GSMSDK v3.0.0 Seeding...');
        
        $this->seedRoles();
        $this->seedUsers();
        $this->seedFirmware();
        $this->seedApiProviders();
        $this->seedEmailSystem();
        $this->seedCms();
        $this->seedThemes();
        $this->seedNavigation();
        $this->seedTelegram();
        $this->seedAiChatbot();
        $this->seedCloudConfig();
        $this->seedPhoneHistory();
        $this->seedBilling();
        $this->seedFileRequests();
        $this->seedHeroSection();
        $this->seedFeatures();
        $this->seedFooter();
        $this->seedAiAssistance();
        $this->seedSiteSettings();
        
        $this->info('✅ Comprehensive seeding completed successfully!');
    }
    
    /**
     * Seed User Roles
     */
    private function seedRoles(): void {
        $this->info('  ↳ Seeding user roles...');
        
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access with all permissions',
                'permissions' => json_encode([
                    'dashboard.view', 'users.manage', 'roles.manage',
                    'firmware.create', 'firmware.update', 'firmware.delete',
                    'files.upload', 'files.download', 'files.delete',
                    'settings.manage', 'billing.manage', 'api.manage',
                    'reports.view', 'logs.view'
                ])
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access to manage firmware and users',
                'permissions' => json_encode([
                    'dashboard.view', 'users.view',
                    'firmware.create', 'firmware.update', 'firmware.delete',
                    'files.upload', 'files.download',
                    'settings.view', 'reports.view'
                ])
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Can manage firmware entries and files',
                'permissions' => json_encode([
                    'firmware.create', 'firmware.update', 'firmware.delete',
                    'firmware.view', 'files.upload', 'files.download'
                ])
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Can view firmware and download files',
                'permissions' => json_encode([
                    'firmware.view', 'files.download', 'reports.view'
                ])
            ],
            [
                'name' => 'API User',
                'slug' => 'api-user',
                'description' => 'API access only',
                'permissions' => json_encode([
                    'api.access', 'firmware.view'
                ])
            ],
            [
                'name' => 'Customer Support',
                'slug' => 'support',
                'description' => 'Customer support access',
                'permissions' => json_encode([
                    'users.view', 'firmware.view',
                    'downloads.view', 'ai-chatbot.manage',
                    'phone-history.view'
                ])
            ]
        ];
        
        foreach ($roles as $roleData) {
            $role = Role::query()->where('slug', $roleData['slug'])->first();
            if (!$role) {
                $role = new Role(array_merge($roleData, [
                    'is_active' => true
                ]));
                $role->save();
                $this->info('    ✅ Created role: ' . $role->name);
            }
        }
    }
    
    /**
     * Seed Users with Profiles
     */
    private function seedUsers(): void {
        $this->info('  ↳ Seeding users with profiles...');
        
        $usersData = [
            [
                'username' => 'superadmin',
                'email' => 'superadmin@gsmsdk.com',
                'password' => password_hash('SuperAdmin123!', PASSWORD_BCRYPT),
                'role_slug' => 'super-admin',
                'profile' => [
                    'phone' => '+1-555-0101',
                    'timezone' => 'America/New_York',
                    'language' => 'en'
                ]
            ],
            [
                'username' => 'admin',
                'email' => 'admin@gsmsdk.com',
                'password' => password_hash('Admin123!', PASSWORD_BCRYPT),
                'role_slug' => 'admin',
                'profile' => [
                    'phone' => '+1-555-0102',
                    'timezone' => 'America/Los_Angeles',
                    'language' => 'en'
                ]
            ],
            [
                'username' => 'editor',
                'email' => 'editor@gsmsdk.com',
                'password' => password_hash('Editor123!', PASSWORD_BCRYPT),
                'role_slug' => 'editor',
                'profile' => [
                    'phone' => '+1-555-0103',
                    'timezone' => 'Europe/London',
                    'language' => 'en'
                ]
            ],
            [
                'username' => 'viewer',
                'email' => 'viewer@gsmsdk.com',
                'password' => password_hash('Viewer123!', PASSWORD_BCRYPT),
                'role_slug' => 'viewer',
                'profile' => [
                    'phone' => '+1-555-0104',
                    'timezone' => 'Asia/Tokyo',
                    'language' => 'en'
                ]
            ],
            [
                'username' => 'support',
                'email' => 'support@gsmsdk.com',
                'password' => password_hash('Support123!', PASSWORD_BCRYPT),
                'role_slug' => 'support',
                'profile' => [
                    'phone' => '+1-555-0105',
                    'timezone' => 'Europe/Berlin',
                    'language' => 'en'
                ]
            ]
        ];
        
        foreach ($usersData as $userData) {
            $existing = User::query()->where('email', $userData['email'])->first();
            if ($existing) {
                continue;
            }
            
            $user = new User([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'status' => 'active'
            ]);
            $user->save();
            
            // Create profile
            $profile = new UserProfile([
                'user_id' => $user->id,
                'phone' => $userData['profile']['phone'],
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($userData['username']) . '&background=00ff88&color=0a0a0f',
                'timezone' => $userData['profile']['timezone'],
                'language' => $userData['profile']['language'],
                'address' => '123 GSMSDK Street, Tech City',
                'preferences' => json_encode([
                    'notifications' => true,
                    'dark_mode' => true,
                    'email_updates' => true
                ])
            ]);
            $profile->save();
            
            // Create files for each user
            for ($i = 1; $i <= 3; $i++) {
                $file = new FileRepository([
                    'user_id' => $user->id,
                    'filename' => $userData['username'] . '_file_' . $i . '.zip',
                    'original_name' => 'firmware_package_' . $i . '.zip',
                    'mime_type' => 'application/zip',
                    'file_size' => (rand(10, 100) * 1048576) . ' bytes',
                    'file_hash' => hash('sha256', 'file-' . $user->id . '-' . $i . '-' . time()),
                    'storage_path' => '/uploads/users/' . $user->id . '/file_' . $i . '.zip',
                    'storage_type' => 'local',
                    'visibility' => 'private',
                    'description' => 'Firmware package ' . $i . ' uploaded by ' . $userData['username'],
                    'category' => 'firmware',
                    'status' => 'active',
                    'download_count' => rand(0, 50)
                ]);
                $file->save();
            }
            
            // Create download history
            for ($i = 1; $i <= 5; $i++) {
                $history = new DownloadHistory([
                    'user_id' => $user->id,
                    'file_id' => $user->id * 10 + $i,
                    'filename' => 'firmware_sample_' . $i . '.zip',
                    'ip_address' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'device_type' => ['desktop', 'mobile', 'tablet'][rand(0, 2)],
                    'country' => ['US', 'UK', 'DE', 'JP', 'CN', 'FR', 'IT', 'ES'][rand(0, 7)],
                    'city' => ['New York', 'London', 'Berlin', 'Tokyo', 'Beijing', 'Paris', 'Rome', 'Madrid'][rand(0, 7)],
                    'metadata' => json_encode([
                        'browser' => 'Chrome ' . rand(100, 120),
                        'os' => 'Windows 10 / macOS / Android',
                        'referrer' => 'https://gsmsdk.com'
                    ])
                ]);
                $history->save();
            }
            
            $this->info('    ✅ Created user: ' . $userData['username']);
        }
    }
    
    /**
     * Seed Firmware Data
     */
    private function seedFirmware(): void {
        $this->info('  ↳ Seeding firmware entries...');
        
        // Already seeded by FirmwareFactory, just verify
        $count = Firmware::count();
        $this->info('    ✅ Firmware entries: ' . $count);
    }
    
    /**
     * Seed API Providers
     */
    private function seedApiProviders(): void {
        $this->info('  ↳ Seeding API providers...');
        
        $providers = [
            [
                'name' => 'OpenAI GPT-4',
                'provider_key' => 'openai',
                'endpoint_url' => 'https://api.openai.com/v1',
                'api_key' => 'sk-' . bin2hex(random_bytes(20)),
                'credentials' => ['organization' => 'org-gsmsdk'],
                'rate_limit' => 1000,
                'timeout' => 60
            ],
            [
                'name' => 'Anthropic Claude-3',
                'provider_key' => 'anthropic',
                'endpoint_url' => 'https://api.anthropic.com/v1',
                'api_key' => 'sk-ant-' . bin2hex(random_bytes(30)),
                'credentials' => [],
                'rate_limit' => 500,
                'timeout' => 60
            ],
            [
                'name' => 'Google Gemini',
                'provider_key' => 'google',
                'endpoint_url' => 'https://generativelanguage.googleapis.com/v1',
                'api_key' => 'AIzaSy' . bin2hex(random_bytes(25)),
                'credentials' => ['project_id' => 'gsmsdk-project'],
                'rate_limit' => 1000,
                'timeout' => 60
            ],
            [
                'name' => 'OpenAI GPT-3.5',
                'provider_key' => 'openai-gpt35',
                'endpoint_url' => 'https://api.openai.com/v1',
                'api_key' => 'sk-' . bin2hex(random_bytes(20)),
                'credentials' => ['organization' => 'org-gsmsdk'],
                'rate_limit' => 2000,
                'timeout' => 30
            ]
        ];
        
        foreach ($providers as $provider) {
            $existing = ApiProvider::query()->where('provider_key', $provider['provider_key'])->first();
            if (!$existing) {
                $apiProvider = new ApiProvider(array_merge($provider, [
                    'is_active' => true,
                    'credentials' => json_encode($provider['credentials'])
                ]));
                $apiProvider->save();
                $this->info('    ✅ Created API provider: ' . $provider['name']);
            }
        }
    }
    
    /**
     * Seed Email System
     */
    private function seedEmailSystem(): void {
        $this->info('  ↳ Seeding email system...');
        
        // Email Config
        $emailConfig = EmailConfig::query()->first();
        if (!$emailConfig) {
            $emailConfig = new EmailConfig([
                'driver' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'noreply@gsmsdk.com',
                'password' => 'encrypted-password-here',
                'encryption' => 'tls',
                'from_address' => 'noreply@gsmsdk.com',
                'from_name' => 'GSMSDK Firmware Service',
                'is_active' => true,
                'settings' => json_encode([
                    'smtp_security' => 'tls',
                    'smtp_auth' => true,
                    'timeout' => 30
                ])
            ]);
            $emailConfig->save();
            $this->info('    ✅ Created email configuration');
        }
        
        // Email Templates
        $templates = [
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to GSMSDK Firmware Service, {{username}}!',
                'html' => '<html><body><h1>Welcome to GSMSDK, {{username}}!</h1><p>Thank you for joining our professional firmware download service.</p><p>Your account has been successfully created and you can now access over 270 firmware entries for 150+ device brands.</p><p><a href="{{login_url}}">Click here to login</a></p></body></html>',
                'variables' => ['username', 'login_url', 'site_name']
            ],
            [
                'name' => 'Password Reset',
                'slug' => 'password-reset',
                'subject' => 'Password Reset Request',
                'html' => '<html><body><h1>Password Reset Request</h1><p>We received a request to reset your password.</p><p>Click the button below to reset your password:</p><p><a href="{{reset_url}}">Reset Password</a></p><p>If you did not request this, please ignore this email.</p></body></html>',
                'variables' => ['reset_url', 'username']
            ],
            [
                'name' => 'Firmware Download',
                'slug' => 'firmware-download',
                'subject' => 'Firmware Download: {{firmware_name}}',
                'html' => '<html><body><h1>Download Started</h1><p>Your download of <strong>{{firmware_name}}</strong> has started.</p><p>Device: {{device_model}}<br>Version: {{version}}<br>Security Patch: {{security_patch}}</p><p><a href="{{download_url}}">Download Link</a></p></body></html>',
                'variables' => ['firmware_name', 'device_model', 'version', 'security_patch', 'download_url']
            ],
            [
                'name' => 'New Firmware Alert',
                'slug' => 'new-firmware',
                'subject' => 'New Firmware Available: {{firmware_name}} for {{device_model}}',
                'html' => '<html><body><h1>New Firmware Update Available!</h1><p>Great news! A new firmware update is now available for your device.</p><p><strong>{{firmware_name}}</strong><br>Device: {{device_model}}<br>Version: {{version}}<br>Security Patch: {{security_patch}}</p><p><a href="{{download_url}}">Download Now</a></p></body></html>',
                'variables' => ['firmware_name', 'device_model', 'version', 'security_patch', 'download_url']
            ],
            [
                'name' => 'IMEI Repair Complete',
                'slug' => 'imei-repair-complete',
                'subject' => 'IMEI Repair Completed for {{device_imei}}',
                'html' => '<html><body><h1>IMEI Repair Complete</h1><p>Your IMEI repair has been successfully completed.</p><p>Device IMEI: {{device_imei}}<br>Status: Completed<br>Repair Method: {{repair_method}}</p><p>Your device should now have full network functionality.</p></body></html>',
                'variables' => ['device_imei', 'repair_method', 'completion_date']
            ],
            [
                'name' => 'FRP Remove Complete',
                'slug' => 'frp-remove-complete',
                'subject' => 'FRP Remove Completed - {{device_model}}',
                'html' => '<html><body><h1>FRP Removal Complete</h1><p>Google Factory Reset Protection has been successfully removed from your device.</p><p>Device: {{device_model}}<br>Android Version: {{android_version}}<br>Status: Completed</p><p>You can now set up your device with a new Google account.</p></body></html>',
                'variables' => ['device_model', 'android_version', 'completion_date']
            ],
            [
                'name' => 'Subscription Confirmation',
                'slug' => 'subscription-confirmation',
                'subject' => 'Subscription Confirmed - {{plan_name}}',
                'html' => '<html><body><h1>Subscription Confirmed</h1><p>Thank you for subscribing to our service!</p><p><strong>{{plan_name}}</strong><br>Amount: {{amount}}<br>Billing Cycle: {{interval}}<br>Next Billing Date: {{next_billing_date}}</p><p>You now have access to all premium features.</p></body></html>',
                'variables' => ['plan_name', 'amount', 'interval', 'next_billing_date']
            ],
            [
                'name' => 'Billing Reminder',
                'slug' => 'billing-reminder',
                'subject' => 'Payment Reminder - {{plan_name}}',
                'html' => '<html><body><h1>Payment Reminder</h1><p>This is a friendly reminder that your subscription payment is due soon.</p><p><strong>{{plan_name}}</strong><br>Amount Due: {{amount}}<br>Due Date: {{due_date}}</p><p><a href="{{billing_url}}">Update Payment Method</a></p></body></html>',
                'variables' => ['plan_name', 'amount', 'due_date', 'billing_url']
            ],
            [
                'name' => 'Support Ticket Created',
                'slug' => 'support-ticket',
                'subject' => 'Support Ticket #{{ticket_id}} - {{subject}}',
                'html' => '<html><body><h1>Support Ticket Created</h1><p>We have received your support request and our team is reviewing it.</p><p>Ticket ID: {{ticket_id}}<br>Subject: {{subject}}<br>Status: Open<br>Priority: {{priority}}</p><p>We will respond within 24-48 hours.</p></body></html>',
                'variables' => ['ticket_id', 'subject', 'priority', 'status']
            ],
            [
                'name' => 'Admin Alert',
                'slug' => 'admin-alert',
                'subject' => 'System Alert: {{alert_type}}',
                'html' => '<html><body><h1>System Alert</h1><p><strong>{{alert_type}}</strong></p><p>{{message}}</p><p>Time: {{timestamp}}</p><p>Severity: {{severity}}</p></body></html>',
                'variables' => ['alert_type', 'message', 'timestamp', 'severity']
            ]
        ];
        
        foreach ($templates as $template) {
            $existing = EmailTemplate::query()->where('slug', $template['slug'])->first();
            if (!$existing) {
                $emailTemplate = new EmailTemplate(array_merge($template, [
                    'is_active' => true
                ]));
                $emailTemplate->save();
                $this->info('    ✅ Created email template: ' . $template['name']);
            }
        }
    }
    
    /**
     * Seed CMS Pages
     */
    private function seedCms(): void {
        $this->info('  ↳ Seeding CMS pages...');
        
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about',
                'template' => 'default',
                'content' => '<h1>About GSMSDK Firmware Service</h1><p>Welcome to GSMSDK, your premier destination for professional firmware download and device repair solutions. Since 2024, we have been providing reliable firmware services for over 150 device brands worldwide.</p><h2>Our Mission</h2><p>To provide fast, secure, and reliable firmware download services with comprehensive device repair solutions including IMEI repair, FRP removal, and advanced flashing tools.</p><h2>Why Choose Us?</h2><ul><li>270+ firmware entries</li><li>150+ device brands</li><li>Enterprise-grade reliability</li><li>24/7 customer support</li><li>Advanced repair tools</li></ul>',
                'meta_tags' => json_encode([
                    'title' => 'About GSMSDK - Professional Firmware Service',
                    'description' => 'Learn about our firmware download service and device repair solutions.',
                    'keywords' => 'firmware, download, repair, android, imei, frp'
                ]),
                'status' => 'published'
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'template' => 'default',
                'content' => '<h1>Contact Us</h1><p>Have questions or need assistance? Reach out to our support team.</p><h2>Get in Touch</h2><form><div><label>Name</label><input type="text" name="name"></div><div><label>Email</label><input type="email" name="email"></div><div><label>Message</label><textarea name="message"></textarea></div><button type="submit">Send Message</button></form>',
                'meta_tags' => json_encode([
                    'title' => 'Contact Us - GSMSDK Firmware Service',
                    'description' => 'Get in touch with our support team for assistance with firmware downloads and device repairs.'
                ]),
                'status' => 'published'
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'template' => 'default',
                'content' => '<h1>Terms of Service</h1><p>Last updated: ' . date('F j, Y') . '</p><h2>1. Acceptance of Terms</h2><p>By accessing this website, you agree to these terms of service...</p><h2>2. Use of Service</h2><p>You agree to use our service only for lawful purposes...</p>',
                'meta_tags' => json_encode([
                    'title' => 'Terms of Service - GSMSDK',
                    'description' => 'Terms and conditions for using GSMSDK firmware service.'
                ]),
                'status' => 'published'
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'template' => 'default',
                'content' => '<h1>Privacy Policy</h1><p>We value your privacy and are committed to protecting your personal information.</p><h2>1. Information We Collect</h2><p>We collect information you provide directly to us...</p><h2>2. How We Use Information</h2><p>We use your information to provide and improve our service...</p>',
                'meta_tags' => json_encode([
                    'title' => 'Privacy Policy - GSMSDK',
                    'description' => 'Learn how we protect your privacy and personal information.'
                ]),
                'status' => 'published'
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'template' => 'faq',
                'content' => '<h1>Frequently Asked Questions</h1><div class="faq-item"><h3>What is IMEI repair?</h3><p>IMEI repair is a process to restore the unique identifier of your device if it has been lost or corrupted.</p></div><div class="faq-item"><h3>What is FRP removal?</h3><p>FRP (Factory Reset Protection) removal allows you to bypass Google account verification after a factory reset.</p></div><div class="faq-item"><h3>How do I download firmware?</h3><p>Browse our firmware database, select your device model, and click download. No registration required for basic downloads.</p></div>',
                'meta_tags' => json_encode([
                    'title' => 'FAQ - GSMSDK Firmware Service',
                    'description' => 'Frequently asked questions about firmware downloads and device repairs.'
                ]),
                'status' => 'published'
            ]
        ];
        
        foreach ($pages as $page) {
            $existing = CmsPage::query()->where('slug', $page['slug'])->first();
            if (!$existing) {
                $cmsPage = new CmsPage(array_merge($page, [
                    'author_id' => 1,
                    'published_at' => now()
                ]));
                $cmsPage->save();
                $this->info('    ✅ Created page: ' . $page['title']);
            }
        }
    }
    
    /**
     * Seed Themes
     */
    private function seedThemes(): void {
        $this->info('  ↳ Seeding themes...');
        
        $themes = [
            [
                'name' => 'GridCN Dark',
                'slug' => 'gridcn-dark',
                'version' => '2.0.0',
                'author' => 'GSMSDK Team',
                'description' => 'Modern dark theme with GridCN design system',
                'settings' => json_encode([
                    'primary_color' => '#00ff88',
                    'secondary_color' => '#ff00aa',
                    'background_color' => '#0a0a0f',
                    'card_background' => '#151520',
                    'text_color' => '#ffffff',
                    'border_radius' => '8px'
                ]),
                'preview_images' => json_encode([
                    '/themes/gridcn-dark/preview-1.png',
                    '/themes/gridcn-dark/preview-2.png',
                    '/themes/gridcn-dark/preview-3.png'
                ]),
                'is_active' => true
            ],
            [
                'name' => 'GridCN Light',
                'slug' => 'gridcn-light',
                'version' => '2.0.0',
                'author' => 'GSMSDK Team',
                'description' => 'Clean light theme with GridCN design system',
                'settings' => json_encode([
                    'primary_color' => '#0066ff',
                    'secondary_color' => '#ff6600',
                    'background_color' => '#ffffff',
                    'card_background' => '#f5f5f5',
                    'text_color' => '#333333',
                    'border_radius' => '8px'
                ]),
                'preview_images' => json_encode([
                    '/themes/gridcn-light/preview-1.png',
                    '/themes/gridcn-light/preview-2.png'
                ]),
                'is_active' => false
            ],
            [
                'name' => 'Cyberpunk',
                'slug' => 'cyberpunk',
                'version' => '1.5.0',
                'author' => 'GSMSDK Team',
                'description' => 'Neon cyberpunk inspired theme',
                'settings' => json_encode([
                    'primary_color' => '#00ffff',
                    'secondary_color' => '#ff00ff',
                    'background_color' => '#0d0d1a',
                    'card_background' => '#1a1a2e',
                    'text_color' => '#e0e0e0',
                    'border_radius' => '4px'
                ]),
                'preview_images' => json_encode([
                    '/themes/cyberpunk/preview-1.png'
                ]),
                'is_active' => false
            ],
            [
                'name' => 'Minimal',
                'slug' => 'minimal',
                'version' => '1.0.0',
                'author' => 'GSMSDK Team',
                'description' => 'Clean and minimal design',
                'settings' => json_encode([
                    'primary_color' => '#2c3e50',
                    'secondary_color' => '#34495e',
                    'background_color' => '#ecf0f1',
                    'card_background' => '#ffffff',
                    'text_color' => '#2c3e50',
                    'border_radius' => '0px'
                ]),
                'preview_images' => json_encode([
                    '/themes/minimal/preview-1.png'
                ]),
                'is_active' => false
            ]
        ];
        
        foreach ($themes as $theme) {
            $existing = Theme::query()->where('slug', $theme['slug'])->first();
            if (!$existing) {
                $themeModel = new Theme($theme);
                $themeModel->save();
                $this->info('    ✅ Created theme: ' . $theme['name']);
            }
        }
    }
    
    /**
     * Seed Navigation Menus
     */
    private function seedNavigation(): void {
        $this->info('  ↳ Seeding navigation menus...');
        
        $menus = [
            [
                'name' => 'Main Navigation',
                'location' => 'header',
                'is_active' => true,
                'sort_order' => 1,
                'items' => [
                    ['title' => 'Home', 'url' => '/', 'target' => '_self', 'order' => 1, 'icon' => ''],
                    ['title' => 'Flash Tool', 'url' => '/flash', 'target' => '_self', 'order' => 2, 'icon' => '⚡'],
                    ['title' => 'Firmware', 'url' => '/firmware', 'target' => '_self', 'order' => 3, 'icon' => '📱'],
                    ['title' => 'IMEI Checker', 'url' => '/imei-checker', 'target' => '_self', 'order' => 4, 'icon' => '🔍'],
                    ['title' => 'Pricing', 'url' => '/pricing', 'target' => '_self', 'order' => 5, 'icon' => '💳'],
                    ['title' => 'Dashboard', 'url' => '/admin', 'target' => '_self', 'order' => 6, 'icon' => '📊']
                ]
            ],
            [
                'name' => 'Footer Menu',
                'location' => 'footer',
                'is_active' => true,
                'sort_order' => 2,
                'items' => [
                    ['title' => 'About', 'url' => '/about', 'target' => '_self', 'order' => 1, 'icon' => ''],
                    ['title' => 'Contact', 'url' => '/contact', 'target' => '_self', 'order' => 2, 'icon' => ''],
                    ['title' => 'Privacy', 'url' => '/privacy', 'target' => '_self', 'order' => 3, 'icon' => ''],
                    ['title' => 'Terms', 'url' => '/terms', 'target' => '_self', 'order' => 4, 'icon' => ''],
                    ['title' => 'FAQ', 'url' => '/faq', 'target' => '_self', 'order' => 5, 'icon' => '']
                ]
            ],
            [
                'name' => 'User Panel',
                'location' => 'sidebar',
                'is_active' => true,
                'sort_order' => 3,
                'items' => [
                    ['title' => 'My Profile', 'url' => '/user/profile', 'target' => '_self', 'order' => 1, 'icon' => '👤'],
                    ['title' => 'My Files', 'url' => '/user/files', 'target' => '_self', 'order' => 2, 'icon' => '📁'],
                    ['title' => 'Download History', 'url' => '/user/downloads', 'target' => '_self', 'order' => 3, 'icon' => '📥'],
                    ['title' => 'Billing', 'url' => '/user/billing', 'target' => '_self', 'order' => 4, 'icon' => '💳'],
                    ['title' => 'Settings', 'url' => '/user/settings', 'target' => '_self', 'order' => 5, 'icon' => '⚙️'],
                    ['title' => 'Logout', 'url' => '/logout', 'target' => '_self', 'order' => 6, 'icon' => '🚪']
                ]
            ]
        ];
        
        foreach ($menus as $menu) {
            $items = $menu['items'];
            unset($menu['items']);
            
            $existing = NavigationMenu::query()->where('location', $menu['location'])->first();
            if (!$existing) {
                $navMenu = new NavigationMenu(array_merge($menu, [
                    'items' => json_encode($items)
                ]));
                $navMenu->save();
                $this->info('    ✅ Created menu: ' . $menu['name']);
            }
        }
    }
    
    /**
     * Seed Telegram Bot
     */
    private function seedTelegram(): void {
        $this->info('  ↳ Seeding Telegram bot...');
        
        $bot = TelegramBot::query()->first();
        if (!$bot) {
            $telegramBot = new TelegramBot([
                'name' => 'GSMSDK Firmware Bot',
                'bot_token' => '1234567890:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnop',
                'bot_username' => 'GSMSDK_Firmware_Bot',
                'is_active' => true,
                'settings' => json_encode([
                    'webhook_url' => 'https://api.gsmsdk.com/telegram/webhook',
                    'allowed_chats' => [931774029],
                    'commands' => ['/start', '/help', '/firmware', '/download', '/imeicheck', '/status'],
                    'download_options' => [
                        'direct_download' => true,
                        'torrent_magnet' => true,
                        'edl_mode' => true,
                        'fastboot_mode' => true,
                        'adb_push' => true
                    ]
                ]),
                'webhook_config' => json_encode([
                    'url' => 'https://api.gsmsdk.com/telegram/webhook',
                    'secret_token' => bin2hex(random_bytes(32)),
                    'max_connections' => 100,
                    'allowed_updates' => ['message', 'callback_query']
                ])
            ]);
            $telegramBot->save();
            $this->info('    ✅ Created Telegram bot');
        }
    }
    
    /**
     * Seed AI Chatbot
     */
    private function seedAiChatbot(): void {
        $this->info('  ↳ Seeding AI chatbot...');
        
        $chatbot = AiChatbotConfig::query()->first();
        if (!$chatbot) {
            $aiChatbot = new AiChatbotConfig([
                'name' => 'GSMSDK Customer Support AI',
                'provider' => 'openai',
                'model' => 'gpt-4',
                'temperature' => 0.7,
                'max_tokens' => 1500,
                'is_active' => true,
                'settings' => json_encode([
                    'api_key' => 'sk-' . bin2hex(random_bytes(32)),
                    'embedding_model' => 'text-embedding-3-small',
                    'streaming' => true,
                    'rate_limit' => 100,
                    'timeout' => 60,
                    'cache_responses' => true
                ]),
                'prompts' => json_encode([
                    'system' => 'You are a helpful firmware download assistant. Help users find firmware, troubleshoot devices, and provide download guidance. Be concise, accurate, and friendly.',
                    'welcome' => 'Hello! I\'m your firmware assistant. I can help you find firmware, check device compatibility, and guide you through the download process. How can I help you today?',
                    'fallback' => 'I\'m not sure about that. Let me connect you with a human support agent.',
                    'farewell' => 'Goodbye! If you need further assistance, feel free to ask.'
                ]),
                'knowledge_base' => json_encode([
                    'firmware_guides' => true,
                    'device_compatibility' => true,
                    'repair_procedures' => true,
                    'troubleshooting' => true,
                    'download_instructions' => true,
                    'imei_repair_steps' => true,
                    'frp_removal_methods}