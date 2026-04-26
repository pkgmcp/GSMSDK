<?php

declare(strict_types=1);

namespace GSMSDK\Models;

use GSMSDK\Core\Model;

/**
 * Flash Log Model
 */
class FlashLog extends Model {
    protected static ?string $table = 'flash_logs';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [
        'device_id',
        'user_id',
        'operation',
        'partition',
        'image_path',
        'options',
        'status',
        'output',
        'duration'
    ];
    protected static array $casts = [
        'id' => 'int',
        'device_id' => 'int',
        'user_id' => 'int',
        'options' => 'json',
        'duration' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    protected static array $hidden = [];
    
    /**
     * Set options from array
     */
    protected function setOptionsAttribute(array $value): void {
        $this->attributes['options'] = json_encode($value);
    }
    
    /**
     * Get options as array
     */
    public function getOptions(): array {
        return json_decode($this->attributes['options'] ?? '[]', true) ?? [];
    }
    
    /**
     * Check if operation was successful
     */
    public function isSuccessful(): bool {
        return $this->status === 'completed';
    }
    
    /**
     * Check if operation failed
     */
    public function isFailed(): bool {
        return $this->status === 'failed';
    }
    
    /**
     * Check if operation is pending
     */
    public function isPending(): bool {
        return $this->status === 'pending';
    }
    
    /**
     * Check if operation is running
     */
    public function isRunning(): bool {
        return $this->status === 'running';
    }
    
    /**
     * Get associated device
     */
    public function device(): ?Device {
        if (!$this->device_id) {
            return null;
        }
        
        return Device::find($this->device_id);
    }
    
    /**
     * Get associated user
     */
    public function user(): ?User {
        if (!$this->user_id) {
            return null;
        }
        
        return User::find($this->user_id);
    }
    
    /**
     * Mark operation as completed
     */
    public function markAsCompleted(): bool {
        $this->status = 'completed';
        return $this->save();
    }
    
     /**
     * Mark operation as failed
     */
    public function markAsFailed(): bool {
        $this->status = 'failed';
        return $this->save();
    }
    
    /**
     * Mark operation as running
     */
    public function markAsRunning(): bool {
        $this->status = 'running';
        return $this->save();
    }
}
