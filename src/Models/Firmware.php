<?php

declare(strict_types=1);

namespace GSMSDK\Models;

use GSMSDK\Core\Model;

/**
 * Firmware Model
 */
class Firmware extends Model {
    protected static ?string $table = 'firmware';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [
        'brand',
        'model',
        'region',
        'version',
        'build_number',
        'firmware_type',
        'file_name',
        'file_size',
        'file_hash',
        'download_url',
        'changelog',
        'status',
        'download_count',
        'rating',
        'is_popular',
        'is_recommended'
    ];
    protected static array $casts = [
        'id' => 'int',
        'download_count' => 'int',
        'rating' => 'int',
        'is_popular' => 'boolean',
        'is_recommended' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    protected static array $hidden = [];
    
    /**
     * Increment download count
     */
    public function incrementDownload(): bool {
        $this->download_count++;
        return $this->save();
    }
    
    /**
     * Update rating
     */
    public function updateRating(int $rating): bool {
        $this->rating = $rating;
        return $this->save();
    }
    
    /**
     * Get firmware details
     */
    public function getDetails(): array {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'model' => $this->model,
            'version' => $this->version,
            'build_number' => $this->build_number,
            'type' => $this->firmware_type,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'hash' => $this->file_hash,
            'url' => $this->download_url,
            'changelog' => $this->changelog,
            'status' => $this->status,
            'downloads' => $this->download_count,
            'rating' => $this->rating,
            'popular' => $this->is_popular,
            'recommended' => $this->is_recommended,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * Get firmware for device
     */
    public static function forDevice(string $brand, string $model, ?string $region = null): array {
        $query = self::query()
            ->where('brand', $brand)
            ->where('model', $model)
            ->where('status', 'active');
        
        if ($region) {
            $query->where(function ($q) use ($region) {
                $q->where('region', $region)
                  ->orWhereNull('region');
            });
        }
        
        return $query->orderBy('version', 'desc')->get();
    }
    
    /**
     * Get latest firmware
     */
    public static function latest(string $brand, string $model): ?self {
        return self::query()
            ->where('brand', $brand)
            ->where('model', $model)
            ->where('status', 'active')
            ->orderBy('version', 'desc')
            ->first();
    }
    
    /**
     * Search firmware
     */
    public static function search(string $query): array {
        return self::query()
            ->where(function ($q) use ($query) {
                $q->where('brand', 'LIKE', '%' . $query . '%')
                  ->orWhere('model', 'LIKE', '%' . $query . '%')
                  ->orWhere('version', 'LIKE', '%' . $query . '%')
                  ->orWhere('file_name', 'LIKE', '%' . $query . '%');
            })
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get popular firmware
     */
    public static function popular(int $limit = 10): array {
        return self::query()
            ->where('is_popular', true)
            ->where('status', 'active')
            ->orderBy('download_count', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get recommended firmware
     */
    public static function recommended(string $brand, string $model): array {
        return self::query()
            ->where('brand', $brand)
            ->where('model', $model)
            ->where('is_recommended', true)
            ->where('status', 'active')
            ->orderBy('version', 'desc')
            ->get();
    }
}
