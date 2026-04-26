<?php

declare(strict_types=1);

namespace App\Controllers\Api\Firmware;

use GSMSDK\Core\Application;
use GSMSDK\Models\Firmware;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

/**
 * Firmware Controller
 */
class FirmwareController {
    protected Application $app;
    
    public function __construct(Application $app) {
        $this->app = $app;
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
            $query->where('model', $model);
        }
        
        if ($region = $request->get('region')) {
            $query->where('region', $region);
        }
        
        if ($type = $request->get('type')) {
            $query->where('firmware_type', $type);
        }
        
        // Sorting
        $sort = $request->get('sort', 'version');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);
        
        // Pagination
        $limit = min($request->get('limit', 20), 100);
        $page = max($request->get('page', 1), 1);
        
        $total = $query->count();
        $firmware = $query->limit($limit)->offset(($page - 1) * $limit)->get();
        
        return Response::json([
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
        
        return Response::json(['firmware' => $firmware->getDetails()]);
    }
    
    /**
     * Get firmware for device
     */
    public function forDevice(Request $request, string $brand, string $model): Response {
        $region = $request->get('region');
        $firmware = Firmware::forDevice($brand, $model, $region);
        
        return Response::json([
            'brand' => $brand,
            'model' => $model,
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
        
        return Response::json(['firmware' => $firmware->getDetails()]);
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
            'query' => $query,
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
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
        ]);
    }
    
    /**
     * Get recommended firmware
     */
    public function recommended(Request $request, string $brand, string $model): Response {
        $firmware = Firmware::recommended($brand, $model);
        
        return Response::json([
            'brand' => $brand,
            'model' => $model,
            'firmware' => array_map(fn($f) => $f->getDetails(), $firmware)
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
        
        $rating = $request->post('rating');
        
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
        $brands = Firmware::query()
            ->select('brand')
            ->where('status', 'active')
            ->distinct()
            ->get()
            ->pluck('brand')
            ->toArray();
        
        return Response::json(['brands' => $brands]);
    }
    
    /**
     * Get models for brand
     */
    public function models(Request $request, string $brand): Response {
        $models = Firmware::query()
            ->select('model')
            ->where('brand', $brand)
            ->where('status', 'active')
            ->distinct()
            ->get()
            ->pluck('model')
            ->toArray();
        
        return Response::json(['brand' => $brand, 'models' => $models]);
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
        
        return Response::json(['success' => true]);
    }
    
    /**
     * Log download
     */
    private function logDownload(Firmware $firmware, Request $request): void {
        // Log download for analytics
        error_log(sprintf(
            '[Firmware Download] ID=%d, Brand=%s, Model=%s, Version=%s, IP=%s, Time=%s',
            $firmware->id,
            $firmware->brand,
            $firmware->model,
            $firmware->version,
            $request->server('REMOTE_ADDR', 'unknown'),
            date('Y-m-d H:i:s')
        ));
    }
}
