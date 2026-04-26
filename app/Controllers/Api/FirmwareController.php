<?php

declare(strict_types=1);

namespace App\Controllers\Api\Firmware;

use GSMSDK\Core\Application;
use GSMSDK\Models\Firmware;
use GSMSDK\Services\FirmwareService;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

/**
 * Firmware Controller
 * 
 * Enhanced with IMEI repair, flash mode, and all major brands
 */
class FirmwareController {
    protected Application $app;
    protected FirmwareService $firmwareService;
    
    public function __construct(Application $app) {
        $this->app = $app;
        $this->firmwareService = new FirmwareService($app);
    }
    
    /**
     * List all firmware
     */
    public function index(Request $request): Response {
        $query = Firmware::query()->where('status', 'active');
        
        // Filters
        if ($brand = $request->get('brand')) {
            $query->where('brand', $brand);
        }
        
        if ($model = $request->get('model')) {
            $query->where('model', 'LIKE', '%' . $model . '%');
        }
        
        if ($region = $request->get('region')) {
            $query->where('region', $region);
        }
        
        if ($type = $request->get('type')) {
            $query->where('firmware_type', $type);
        }
        
        if ($securityPatch = $request->get('security_patch')) {
            $query->where('security_patch', '>=', $securityPatch);
        }
        
        if ($androidVersion = $request->get('android_version')) {
            $query->where('android_version', 'LIKE', $androidVersion . '%');
        }
        
        if ($hasImeiRepair = $request->get('imei_repair')) {
            $query->where('imei_repair_supported', true);
        }
        
        if ($hasFrpr = $request->get('frp_remove')) {
            $query->where('frp_remove_supported', true);
        }
        
        if ($hyperos = $request->get('hyperos')) {
            $query->where('firmware_type', 'hyperos');
        }
        
        if ($cameraSms = $request->get('camera_sms_working')) {
            $query->where('camera_sms_working', true);
        }
        
        if ($ota = $request->get('ota_supported')) {
            $query->where('ota_supported', true);
        }
        
        // Sorting
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);
        
        // Pagination
        $limit = min($request->get('limit', 20), 100);
        $page = max($request->get('page', 1), 1);
        
        $total = $query->count();
        $firmware = $query->limit($limit)->offset(($page - 1) * $limit)->get();
        
        return Response::json([
            'success' => true,
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware),
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    /**
     * Get firmware details
     */
    public function show(Request $request, int $id): Response {
        $firmware = Firmware::find($id);
        
        if (!$firmware) {
            return Response::json(['error' => 'Firmware not found'], 404);
        }
        
        // Increment download count
        $firmware->incrementDownload();
        
        return Response::json([
            'success' => true,
            'firmware' => $firmware->getDetails()
        ]);
    }
    
    /**
     * Get firmware for device
     */
    public function forDevice(Request $request, string $brand, string $model): Response {
        $region = $request->get('region');
        $firmware = Firmware::forDevice($brand, $model, $region);
        
        return Response::json([
            'success' => true,
            'brand' => $brand,
            'model' => $model,
            'region' => $region,
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get latest firmware for device
     */
    public function latest(Request $request, string $brand, string $model): Response {
        $firmware = Firmware::latest($brand, $model);
        
        if (!$firmware) {
            return Response::json(['error' => 'No firmware found'], 404);
        }
        
        return Response::json([
            'success' => true,
            'firmware' => $firmware->getDetails()
        ]);
    }
    
    /**
     * Get recommended firmware for device
     */
    public function recommended(Request $request, string $brand, string $model): Response {
        $firmware = Firmware::recommended($brand, $model);
        
        return Response::json([
            'success' => true,
            'brand' => $brand,
            'model' => $model,
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Search firmware
     */
    public function search(Request $request): Response {
        $query = $request->get('q');
        
        if (empty($query)) {
            return Response::json(['error' => 'Search query required'], 400);
        }
        
        $firmware = Firmware::search($query);
        
        return Response::json([
            'success' => true,
            'query' => $query,
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Search firmware by criteria
     */
    public function searchByCriteria(Request $request): Response {
        $brand = $request->get('brand') ?? '';
        $model = $request->get('model') ?? '';
        $type = $request->get('type') ?? '';
        $region = $request->get('region') ?? '';
        
        $firmware = Firmware::searchByCriteria($brand, $model, $type, $region);
        
        return Response::json([
            'success' => true,
            'filters' => [
                'brand' => $brand,
                'model' => $model,
                'type' => $type,
                'region' => $region
            ],
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get popular firmware
     */
    public function popular(Request $request): Response {
        $limit = min($request->get('limit', 10), 50);
        $firmware = Firmware::popular($limit);
        
        return Response::json([
            'success' => true,
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get firmware by type
     */
    public function byType(Request $request, string $type): Response {
        $firmware = Firmware::byType($type);
        
        return Response::json([
            'success' => true,
            'type' => $type,
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get HyperOS firmware
     */
    public function hyperos(Request $request): Response {
        $firmware = Firmware::hyperos();
        
        return Response::json([
            'success' => true,
            'type' => 'hyperos',
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get firmware with IMEI repair support
     */
    public function imeiRepair(Request $request): Response {
        $firmware = Firmware::imeiRepairSupported();
        
        return Response::json([
            'success' => true,
            'feature' => 'imei_repair',
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get firmware with FRP remove support
     */
    public function frpRemove(Request $request): Response {
        $firmware = Firmware::frpRemoveSupported();
        
        return Response::json([
            'success' => true,
            'feature' => 'frp_remove',
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get firmware with security patch
     */
    public function securityPatch(Request $request): Response {
        $patchDate = $request->get('date');
        
        if (!$patchDate) {
            return Response::json(['error' => 'Date parameter required'], 400);
        }
        
        $firmware = Firmware::withSecurityPatch($patchDate);
        
        return Response::json([
            'success' => true,
            'security_patch' => $patchDate,
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get firmware by Android version
     */
    public function byAndroidVersion(Request $request, string $androidVersion): Response {
        $firmware = Firmware::latestByAndroidVersion($androidVersion);
        
        return Response::json([
            'success' => true,
            'android_version' => $androidVersion,
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get Google factory images
     */
    public function googleFactoryImages(Request $request): Response {
        $brand = 'google';
        $models = $request->get('models', []);
        $versions = $request->get('versions', []);
        
        $query = Firmware::query()
            ->where('brand', $brand)
            ->where('status', 'active')
            ->where('firmware_type', 'official')
            ->where('flash_mode_supported', true);
        
        if (!empty($models)) {
            $query->whereIn('model', (array)$models);
        }
        
        if (!empty($versions)) {
            $query->whereIn('version', (array)$versions);
        }
        
        $firmware = $query->orderBy('version', 'desc')->get();
        
        return Response::json([
            'success' => true,
            'brand' => $brand,
            'type' => 'factory_images',
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get Google OTA updates
     */
    public function googleOtaUpdates(Request $request): Response {
        $brand = 'google';
        $models = $request->get('models', []);
        
        $query = Firmware::query()
            ->where('brand', $brand)
            ->where('status', 'active')
            ->where('firmware_type', 'official')
            ->where('ota_supported', true);
        
        if (!empty($models)) {
            $query->whereIn('model', (array)$models);
        }
        
        $firmware = $query->orderBy('version', 'desc')->get();
        
        return Response::json([
            'success' => true,
            'brand' => $brand,
            'type' => 'ota_updates',
            'count' => count($firmware),
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get Google device models
     */
    public function googleModels(Request $request): Response {
        $models = Firmware::query()
            ->select('model')
            ->where('brand', 'google')
            ->where('status', 'active')
            ->distinct()
            ->orderBy('model')
            ->get()
            ->pluck('model')
            ->toArray();
        
        return Response::json([
            'success' => true,
            'brand' => 'google',
            'count' => count($models),
            'models' => $models
        ]);
    }
    
    /**
     * Download firmware
     */
    public function download(Request $request, int $id): Response {
        $firmware = Firmware::find($id);
        
        if (!$firmware) {
            return Response::json(['error' => 'Firmware not found'], 404);
        }
        
        // Increment download count
        $firmware->incrementDownload();
        
        // Log download
        $this->logDownload($firmware, $request);
        
        return Response::json([
            'success' => true,
            'firmware' => $firmware->getDetails(),
            'download_url' => $firmware->download_url,
            'file_hash' => $firmware->file_hash,
            'file_size' => $firmware->file_size,
            'message' => 'Download initiated'
        ]);
    }
    
    /**
     * Rate firmware
     */
    public function rate(Request $request, int $id): Response {
        $firmware = Firmware::find($id);
        
        if (!$firmware) {
            return Response::json(['error' => 'Firmware not found'], 404);
        }
        
        $rating = (int)$request->post('rating');
        
        if ($rating < 1 || $rating > 5) {
            return Response::json(['error' => 'Rating must be between 1 and 5'], 400);
        }
        
        $firmware->updateRating($rating);
        
        return Response::json([
            'success' => true,
            'rating' => $firmware->rating
        ]);
    }
    
    /**
     * Get brands list
     */
    public function brands(Request $request): Response {
        $brands = Firmware::getAllBrands();
        
        return Response::json([
            'success' => true,
            'count' => count($brands),
            'brands' => $brands
        ]);
    }
    
    /**
     * Get models for brand
     */
    public function models(Request $request, string $brand): Response {
        $models = Firmware::getModelsForBrand($brand);
        
        return Response::json([
            'success' => true,
            'brand' => $brand,
            'count' => count($models),
            'models' => $models
        ]);
    }
    
    /**
     * Get regions for device
     */
    public function regions(Request $request, string $brand, string $model): Response {
        $regions = Firmware::getRegionsForDevice($brand, $model);
        
        return Response::json([
            'success' => true,
            'brand' => $brand,
            'model' => $model,
            'regions' => $regions
        ]);
    }
    
    /**
     * Sync from external sources
     */
    public function sync(Request $request): Response {
        $this->app->auth->middleware($request, 'admin');
        
        $results = $this->firmwareService->refreshFromExternal();
        
        return Response::json([
            'success' => true,
            'results' => $results
        ]);
    }
    
    /**
     * Get firmware statistics
     */
    public function statistics(Request $request): Response {
        return Response::json([
            'success' => true,
            'statistics' => $this->firmwareService->getStatistics()
        ]);
    }
    
    /**
     * Get all brands with models (for dropdowns)
     */
    public function brandsWithModels(Request $request): Response {
        $brands = Firmware::getAllBrands();
        $result = [];
        
        foreach ($brands as $brand) {
            $result[$brand] = Firmware::getModelsForBrand($brand);
        }
        
        return Response::json([
            'success' => true,
            'brands' => $result
        ]);
    }
    
    /**
     * Create firmware (Admin)
     */
    public function create(Request $request): Response {
        $this->app->auth->middleware($request, 'admin');
        
        $data = $request->all();
        $required = ['brand', 'model', 'version', 'file_name', 'file_hash', 'download_url'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return Response::json(['error' => "Field {$field} is required"], 400);
            }
        }
        
        $firmware = new Firmware($data);
        $firmware->save();
        
        return Response::json([
            'success' => true,
            'message' => 'Firmware created successfully',
            'firmware' => $firmware->getDetails()
        ], 201);
    }
    
    /**
     * Update firmware (Admin)
     */
    public function update(Request $request, int $id): Response {
        $this->app->auth->middleware($request, 'admin');
        
        $firmware = Firmware::find($id);
        
        if (!$firmware) {
            return Response::json(['error' => 'Firmware not found'], 404);
        }
        
        $data = $request->all();
        $firmware->fill($data);
        $firmware->save();
        
        return Response::json([
            'success' => true,
            'message' => 'Firmware updated successfully',
            'firmware' => $firmware->getDetails()
        ]);
    }
    
    /**
     * Delete firmware (Admin)
     */
    public function delete(Request $request, int $id): Response {
        $this->app->auth->middleware($request, 'admin');
        
        $firmware = Firmware::find($id);
        
        if (!$firmware) {
            return Response::json(['error' => 'Firmware not found'], 404);
        }
        
        $firmware->delete();
        
        return Response::json([
            'success' => true,
            'message' => 'Firmware deleted successfully'
        ]);
    }
    
    /**
     * Bulk import firmware (Admin)
     */
    public function bulkImport(Request $request): Response {
        $this->app->auth->middleware($request, 'admin');
        
        $items = $request->post('items', []);
        
        if (empty($items) || !is_array($items)) {
            return Response::json(['error' => 'Items array required'], 400);
        }
        
        $imported = 0;
        $errors = [];
        
        foreach ($items as $item) {
            try {
                $firmware = new Firmware($item);
                $firmware->save();
                $imported++;
            } catch (\Exception $e) {
                $errors[] = [
                    'item' => $item,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return Response::json([
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'message' => "Imported {$imported} firmware entries"
        ]);
    }
    
    /**
     * Log download
     */
    private function logDownload(Firmware $firmware, Request $request): void {
        error_log(sprintf(
            '[Firmware Download] ID=%d, Brand=%s, Model=%s, Version=%s, Type=%s, SecurityPatch=%s, AndroidVersion=%s, IMEIRepair=%d, FRPRemove=%d, IP=%s, Time=%s',
            $firmware->id,
            $firmware->brand,
            $firmware->model,
            $firmware->version,
            $firmware->firmware_type,
            $firmware->security_patch,
            $firmware->android_version,
            $firmware->imei_repair_supported ? 1 : 0,
            $firmware->frp_remove_supported ? 1 : 0,
            $request->server('REMOTE_ADDR', 'unknown'),
            date('Y-m-d H:i:s')
        ));
    }
}
