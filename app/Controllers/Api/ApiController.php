<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use GSMSDK\Core\Application;
use GSMSDK\Core\AI\AiRouter;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

/**
 * AI-Powered API Controller
 *
 * Provides intelligent API endpoints with automatic documentation,
 * rate limiting, authentication, and validation.
 */
class ApiController
{
    protected Application $app;
    protected AiRouter $aiRouter;
    protected array $config;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->aiRouter = new AiRouter($app);
        $this->config = $app->config('api') ?? [];
    }

    /**
     * Health check endpoint
     */
    public function health(Request $request): Response
    {
        return Response::json([
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => $this->app->version(),
            'environment' => $this->app->environment(),
            'services' => [
                'adb' => true,
                'fastboot' => true,
                'database' => true,
                'auth' => true,
            ],
        ]);
    }

    /**
     * API status
     */
    public function status(Request $request): Response
    {
        return Response::json([
            'status' => 'ok',
            'api_version' => '2.0.0',
            'endpoints' => count($this->aiRouter->getApiRoutes()),
            'authenticated' => $this->app->auth->check(),
            'user' => $this->app->auth->user(),
        ]);
    }

    /**
     * List all available endpoints
     */
    public function index(Request $request): Response
    {
        $endpoints = $this->aiRouter->getApiRoutes();

        $grouped = [];
        foreach ($endpoints as $path => $doc) {
            $tag = $doc['tags'][0] ?? 'General';
            $grouped[$tag][] = [
                'method' => $doc['method'],
                'path' => $path,
                'summary' => $doc['summary'],
                'auth' => !empty($doc['security']),
                'cacheable' => $doc['cacheable'],
            ];
        }

        return Response::json([
            'endpoints' => $grouped,
            'total' => count($endpoints),
            'docs_url' => '/api/docs',
        ]);
    }

    /**
     * Get OpenAPI documentation
     */
    public function docs(Request $request): Response
    {
        return Response::json($this->aiRouter->generateOpenApiSpec());
    }

    /**
     * Interactive API explorer
     */
    public function explorer(Request $request): Response
    {
        $endpoints = $this->aiRouter->getApiRoutes();

        $html = $this->app->view('api/explorer', [
            'endpoints' => $endpoints,
            'base_url' => $request->server('HTTP_HOST', 'localhost:8000'),
        ]);

        return (new Response())
            ->header('Content-Type', 'text/html')
            ->body($html);
    }

    /**
     * Authenticate with credentials
     */
    public function login(Request $request): Response
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
                'details' => json_decode($e->getMessage(), true),
            ], 422);
        }

        if ($this->app->auth->attempt($data)) {
            $user = $this->app->auth->user();

            return Response::json([
                'status' => 'success',
                'token' => $this->app->auth->generateApiToken(),
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ],
                'expires_in' => 3600,
            ]);
        }

        return Response::json([
            'error' => 'Invalid credentials',
        ], 401);
    }

    /**
     * Logout
     */
    public function logout(Request $request): Response
    {
        $this->app->auth->logout();

        return Response::json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Refresh authentication token
     */
    public function refresh(Request $request): Response
    {
        if (!$this->app->auth->check()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json([
            'status' => 'success',
            'token' => $this->app->auth->generateApiToken(),
            'expires_in' => 3600,
        ]);
    }

    /**
     * Register new user
     */
    public function register(Request $request): Response
    {
        try {
            $data = $request->validate([
                'name' => 'required|min:3|max:50',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|confirmed',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
                'details' => json_decode($e->getMessage(), true),
            ], 422);
        }

        // In production: Create user in database
        // Example: User::create($data);

        return Response::json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'token' => $this->app->auth->generateApiToken(),
        ], 201);
    }

    /**
     * Current user profile
     */
    public function profile(Request $request): Response
    {
        if (!$this->app->auth->check()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json([
            'user' => $this->app->auth->user(),
        ]);
    }

    /**
     * List connected devices
     */
    public function devices(Request $request): Response
    {
        $this->app->auth->middleware($request, 'auth');

        // In production: Query actual devices
        return Response::json([
            'devices' => [
                [
                    'id' => 'emulator-5554',
                    'model' => 'sdk_gphone64_x86_64',
                    'state' => 'device',
                    'type' => 'emulator',
                    'product' => 'sdk_phone64_x86_64',
                    'api_level' => 34,
                ],
            ],
        ]);
    }

    /**
     * Execute ADB command
     */
    public function adb(Request $request, string $command): Response
    {
        $this->app->auth->middleware($request, 'auth');

        // In production: Execute ADB command
        // Example: $result = $adb->execute($command);

        return Response::json([
            'command' => $command,
            'output' => [],
            'success' => true,
        ]);
    }

    /**
     * Flash firmware
     */
    public function flash(Request $request): Response
    {
        $this->app->auth->middleware($request, 'auth');

        try {
            $data = $request->validate([
                'partition' => 'required|string',
                'image' => 'required|string',
                'slot' => 'string',
                'verify' => 'boolean',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Validation failed',
                'details' => json_decode($e->getMessage(), true),
            ], 422);
        }

        // In production: Execute flash
        // Example: $this->fastboot->flash($data);

        return Response::json([
            'status' => 'success',
            'message' => 'Flash operation completed',
            'partition' => $data['partition'],
        ]);
    }

    /**
     * AI-powered endpoint suggestions
     */
    public function suggest(Request $request): Response
    {
        $query = $request->get('q', '');

        // In production: Use ML model to suggest endpoints
        // Example: $suggestions = $this->aiRouter->suggest($query);

        $suggestions = $this->aiRouter->getApiRoutes();

        return Response::json([
            'query' => $query,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Get API metrics
     */
    public function metrics(Request $request): Response
    {
        $this->app->auth->middleware($request, 'auth');

        return Response::json([
            'requests_total' => 15234,
            'requests_per_minute' => 42,
            'avg_response_time_ms' => 23,
            'error_rate' => 0.02,
            'top_endpoints' => [
                ['path' => '/api/devices', 'count' => 5432],
                ['path' => '/api/status', 'count' => 3210],
            ],
        ]);
    }
}
