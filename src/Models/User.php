<?php

declare(strict_types=1);

namespace GSMSDK\Models;

use GSMSDK\Core\Model;

/**
 * User Model
 */
class User extends Model {
    protected static ?string $table = 'users';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'is_admin',
        'active'
    ];
    protected static array $casts = [
        'id' => 'int',
        'is_admin' => 'boolean',
        'active' => 'boolean',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    protected static array $hidden = [
        'password',
        'api_token'
    ];
    
    /**
     * Hash password before saving
     */
    protected function setPasswordAttribute(string $value): void {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->attributes['password']);
    }
    
    /**
     * Generate API token
     */
    public function generateApiToken(): string {
        $token = bin2hex(random_bytes(40));
        $this->api_token = $token;
        $this->save();
        
        return $token;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool {
        return (bool) $this->is_admin;
    }
    
    /**
     * Check if user is active
     */
    public function isActive(): bool {
        return (bool) $this->active;
    }
    
    /**
     * Get user's devices
     */
    public function devices(): array {
        return Device::query()
            ->where('user_id', $this->id)
            ->get();
    }
    
    /**
     * Get user's flash logs
     */
    public function flashLogs(): array {
        return FlashLog::query()
            ->where('user_id', $this->id)
            ->get();
    }
}
