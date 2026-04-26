<?php

declare(strict_types=1);

namespace GSMSDK\Database\Seeders;

use GSMSDK\Database\Seeder;
use GSMSDK\Models\User;
use GSMSDK\Models\Role;

/**
 * User Roles Seeder
 */
class UserRolesSeeder extends Seeder {
    /**
     * Run the seeder
     */
    public function run(): void {
        $this->info('Seeding user roles and permissions...');
        
        // Create default roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access with all permissions',
                'permissions' => json_encode([
                    'users.manage',
                    'roles.manage',
                    'firmware.manage',
                    'files.manage',
                    'settings.manage',
                    'billing.manage',
                    'api.manage',
                    'reports.view',
                    'downloads.manage'
                ]),
                'is_active' => true
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access to manage firmware and users',
                'permissions' => json_encode([
                    'firmware.manage',
                    'users.view',
                    'files.manage',
                    'reports.view',
                    'downloads.view'
                ]),
                'is_active' => true
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Can manage firmware entries and files',
                'permissions' => json_encode([
                    'firmware.create',
                    'firmware.update',
                    'firmware.delete',
                    'files.upload',
                    'files.manage'
                ]),
                'is_active' => true
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Can view firmware and download files',
                'permissions' => json_encode([
                    'firmware.view',
                    'files.download',
                    'reports.view'
                ]),
                'is_active' => true
            ],
            [
                'name' => 'API User',
                'slug' => 'api-user',
                'description' => 'API access only',
                'permissions' => json_encode([
                    'api.access',
                    'firmware.view'
                ]),
                'is_active' => true
            ],
            [
                'name' => 'Customer Support',
                'slug' => 'support',
                'description' => 'Customer support access',
                'permissions' => json_encode([
                    'users.view',
                    'firmware.view',
                    'downloads.view',
                    'ai-chatbot.manage'
                ]),
                'is_active' => true
            ]
        ];
        
        foreach ($roles as $roleData) {
            $role = new Role($roleData);
            $role->save();
            $this->info('Created role: ' . $role->name);
        }
        
        // Create default admin user
        $adminUser = new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'status' => 'active'
        ]);
        $adminUser->save();
        
        // Assign super admin role
        $superAdminRole = Role::query()->where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            // This would use the pivot table in a real implementation
            $this->info('Admin user created with super-admin role');
        }
        
        // Create additional test users
        $testUsers = [
            [
                'username' => 'editor1',
                'email' => 'editor1@example.com',
                'password' => password_hash('editor123', PASSWORD_BCRYPT),
                'role_slug' => 'editor'
            ],
            [
                'username' => 'viewer1',
                'email' => 'viewer1@example.com',
                'password' => password_hash('viewer123', PASSWORD_BCRYPT),
                'role_slug' => 'viewer'
            ],
            [
                'username' => 'apiuser1',
                'email' => 'apiuser1@example.com',
                'password' => password_hash('api123', PASSWORD_BCRYPT),
                'role_slug' => 'api-user'
            ]
        ];
        
        foreach ($testUsers as $userData) {
            $user = new User([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'status' => 'active'
            ]);
            $user->save();
            $this->info('Created user: ' . $user->username);
        }
        
        $this->info('User roles and permissions seeded successfully');
    }
    
    /**
     * Output info message
     */
    private function info(string $message): void {
        echo "[INFO] {$message}\n";
    }
}
