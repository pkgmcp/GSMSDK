<?php

declare(strict_types=1);

namespace GSMSDK\Models;

use GSMSDK\Core\Model;

/**
 * Firmware Model
 * 
 * Enhanced with IMEI repair, flash mode, and security patch tracking
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
        'security_patch',
        'android_version',
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
        'is_recommended',
        'imei_repair_supported',
        'flash_mode_supported',
        'adb_mode_supported',
        'frp_remove_supported',
        'camera_sms_working',
        'ota_supported',
        'factory_reset_safe'
    ];
    protected static array $casts = [
        'id' => 'int',
        'download_count' => 'int',
        'rating' => 'int',
        'is_popular' => 'boolean',
        'is_recommended' => 'boolean',
        'imei_repair_supported' => 'boolean',
        'flash_mode_supported' => 'boolean',
        'adb_mode_supported' => 'boolean',
        'frp_remove_supported' => 'boolean',
        'camera_sms_working' => 'boolean',
        'ota_supported' => 'boolean',
        'factory_reset_safe' => 'boolean',
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
            'security_patch' => $this->security_patch,
            'android_version' => $this->android_version,
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
            'features' => [
                'imei_repair' => $this->imei_repair_supported,
                'flash_mode' => $this->flash_mode_supported,
                'adb_mode' => $this->adb_mode_supported,
                'frp_remove' => $this->frp_remove_supported,
                'camera_sms_working' => $this->camera_sms_working,
                'ota_supported' => $this->ota_supported,
                'factory_reset_safe' => $this->factory_reset_safe
            ],
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
     * Get latest firmware for device
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
                  ->orWhere('build_number', 'LIKE', '%' . $query . '%')
                  ->orWhere('file_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('changelog', 'LIKE', '%' . $query . '%');
            })
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Search firmware by specific criteria
     */
    public static function searchByCriteria(string $brand, ?string $model = null, ?string $type = null, ?string $region = null): array {
        $query = self::query()->where('status', 'active');
        
        if ($brand) {
            $query->where('brand', $brand);
        }
        
        if ($model) {
            $query->where('model', 'LIKE', '%' . $model . '%');
        }
        
        if ($type) {
            $query->where('firmware_type', $type);
        }
        
        if ($region) {
            $query->where('region', $region);
        }
        
        return $query->orderBy('version', 'desc')->get();
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
    
    /**
     * Get firmware by type
     */
    public static function byType(string $type): array {
        return self::query()
            ->where('firmware_type', $type)
            ->where('status', 'active')
            ->get();
    }
    
    /**
     * Get HyperOS firmware
     */
    public static function hyperos(): array {
        return self::query()
            ->where('firmware_type', 'hyperos')
            ->where('status', 'active')
            ->orderBy('version', 'desc')
            ->get();
    }
    
    /**
     * Get firmware with IMEI repair support
     */
    public static function imeiRepairSupported(): array {
        return self::query()
            ->where('imei_repair_supported', true)
            ->where('status', 'active')
            ->get();
    }
    
    /**
     * Get firmware with FRP remove support
     */
    public static function frpRemoveSupported(): array {
        return self::query()
            ->where('frp_remove_supported', true)
            ->where('status', 'active')
            ->get();
    }
    
    /**
     * Get firmware by Android version
     */
    public static function latestByAndroidVersion(string $androidVersion): array {
        return self::query()
            ->where('android_version', 'LIKE', $androidVersion . '%')
            ->where('status', 'active')
            ->orderBy('version', 'desc')
            ->get();
    }
    
    /**
     * Get all brands
     */
    public static function getAllBrands(): array {
        return self::query()
            ->select('brand')
            ->distinct()
            ->where('status', 'active')
            ->orderBy('brand')
            ->get()
            ->pluck('brand')
            ->toArray();
    }
    
    /**
     * Get models for brand
     */
    public static function getModelsForBrand(string $brand): array {
        return self::query()
            ->select('model')
            ->distinct()
            ->where('brand', $brand)
            ->where('status', 'active')
            ->orderBy('model')
            ->get()
            ->pluck('model')
            ->toArray();
    }
    
    /**
     * Get all regions for device
     */
    public static function getRegionsForDevice(string $brand, string $model): array {
        $regions = self::query()
            ->select('region')
            ->distinct()
            ->where('brand', $brand)
            ->where('model', $model)
            ->whereNotNull('region')
            ->where('status', 'active')
            ->pluck('region');
        
        return array_unique($regions);
    }
    
    /**
     * Get Lenovo unbrick firmware
     */
    public static function lenovoUnbrick(): array {
        return self::query()
            ->where('brand', 'lenovo')
            ->where('firmware_type', 'stock')
            ->where('status', 'active')
            ->orderBy('model')
            ->get();
    }
    
    /**
     * Get OnePlus unbrick firmware
     */
    public static function oneplusUnbrick(): array {
        return self::query()
            ->where('brand', 'oneplus')
            ->where('firmware_type', 'stock')
            ->where('status', 'active')
            ->orderBy('model')
            ->orderBy('version', 'desc')
            ->get();
    }
    
    /**
     * Get stock firmware
     */
    public static function stockFirmware(): array {
        return self::query()
            ->where('firmware_type', 'stock')
            ->where('status', 'active')
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }
    
    /**
     * Get unbrick capable firmware
     */
    public static function unbrickCapable(): array {
        return self::query()
            ->where(function ($query) {
                $query->where('firmware_type', 'stock')
                      ->orWhere('imei_repair_supported', true)
                      ->orWhere('frp_remove_supported', true);
            })
            ->where('status', 'active')
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }
}
