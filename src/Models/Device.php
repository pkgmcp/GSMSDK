<?php

declare(strict_types=1);

namespace GSMSDK\Models;

use GSMSDK\Core\Model;

/**
 * Device Model
 */
class Device extends Model {
    protected static ?string $table = 'devices';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [
        'serial',
        'model',
        'product',
        'manufacturer',
        'state',
        'type',
        'os_version',
        'sdk_version',
        'authorized',
        'online',
        'properties',
        'user_id'
    ];
    protected static array $casts = [
        'id' => 'int',
        'authorized' => 'boolean',
        'online' => 'boolean',
        'properties' => 'json',
        'user_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    /**
     * Set properties from array
     */
    protected function setPropertiesAttribute(array $value): void {
        $this->attributes['properties'] = json_encode($value);
    }
    
    /**
     * Get properties as array
     */
    public function getProperties(): array {
        return json_decode($this->attributes['properties'] ?? '[]', true) ?? [];
    }
    
    /**
     * Get specific property
     */
    public function getProperty(string $key): mixed {
        $properties = $this->getProperties();
        return $properties[$key] ?? null;
    }
    
    /**
     * Authorize device
     */
    public function authorize(): bool {
        $this->authorized = true;
        $this->state = 'device';
        return $this->save();
    }
    
    /**
     * Unauthorize device
     */
    public function unauthorize(): bool {
        $this->authorized = false;
        $this->state = 'unauthorized';
        return $this->save();
    }
    
    /**
     * Set device online
     */
    public function setOnline(): bool {
        $this->online = true;
        return $this->save();
    }
    
    /**
     * Set device offline
     */
    public function setOffline(): bool {
        $this->online = false;
        $this->state = 'offline';
        return $this->save();
    }
    
    /**
     * Get user owner
     */
    public function user(): ?User {
        if (!$this->user_id) {
            return null;
        }
        
        return User::find($this->user_id);
    }
    
    /**
     * Get flash logs
     */
    public function flashLogs(): array {
        return FlashLog::query()
            ->where('device_id', $this->id)
            ->get();
    }
    
    /**
     * Get display name
     */
    public function getDisplayName(): string {
        return $this->model ?: $this->serial;
    }
}
