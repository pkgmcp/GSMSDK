<?php

declare(strict_types=1);

namespace GSMSDK\Middleware;

use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

/**
 * API-Specific Middleware
 */
class ApiMiddleware
{
    /**
     * Verify API key
     */
    public static function verifyApiKey(Request $request): ?Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');

        if (empty($apiKey)) {
            return Response::json([
                'error' => 'API key required',
                'message' => 'Please provide X-API-Key header or api_key parameter',
            ], 401);
        }

        // In production: Verify against database
        // Example: if (!ApiKey::isValid($apiKey)) { ... }

        // Demo check
        if ($apiKey !== 'demo-api-key') {
            return Response::json([
                'error' => 'Invalid API key',
            ], 403);
        }

        return null;
    }

    /**
     * Apply rate limiting for API
     */
    public static function throttle(Request $request): ?Response
    {
        static $cache = [];

        $ip = $request->server('REMOTE_ADDR', 'unknown');
        $key = 'api_throttle:' . $ip;
        $now = time();
        $window = 60; // 1 minute
        $max = 100; // 100 requests per minute

        if (!isset($cache[$key])) {
            $cache[$key] = ['count' => 0, 'window' => $now];
        }

        // Reset window if expired
        if ($now - $cache[$key]['window'] > $window) {
            $cache[$key] = ['count' => 0, 'window' => $now];
        }

        $cache[$key]['count']++;

        if ($cache[$key]['count'] > $max) {
            return Response::json([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again in ' . $window . ' seconds.',
                'retry_after' => $window,
            ], 429);
        }

        // Add rate limit headers
        header('X-RateLimit-Limit: ' . $max);
        header('X-RateLimit-Remaining: ' . max(0, $max - $cache[$key]['count']));
        header('X-RateLimit-Reset: ' . ($cache[$key]['window'] + $window));

        return null;
    }

    /**
     * Ensure JSON request
     */
    public static function requireJson(Request $request): ?Response
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return Response::json([
                'error' => 'Unsupported Media Type',
                'message' => 'This endpoint requires JSON',
            ], 415);
        }

        return null;
    }

    /**
     * Log API request
     */
    public static function logRequest(Request $request): void
    {
        $logEntry = sprintf(
            '[API] %s %s %s %s %s',
            date('Y-m-d H:i:s'),
            $request->getMethod(),
            $request->getUri(),
            $request->server('REMOTE_ADDR', 'unknown'),
            $request->header('User-Agent', 'unknown')
        );

        error_log($logEntry);
    }

    /**
     * Validate content type
     */
    public static function validateContentType(Request $request): ?Response
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request->header('Content-Type', '');

            if (!str_contains($contentType, 'application/json') &&
                !str_contains($contentType, 'multipart/form-data')) {
                return Response::json([
                    'error' => 'Unsupported Media Type',
                    'message' => 'Content-Type must be application/json or multipart/form-data',
                ], 415);
            }
        }

        return null;
    }

    /**
     * Add CORS headers
     */
    public static function addCorsHeaders(Request $request): Response
    {
        $response = new Response();

        $origin = $request->header('Origin', '*');
        $allowedOrigins = ['http://localhost:8000', 'http://localhost:3000'];

        if (in_array($origin, $allowedOrigins) || $origin === '*') {
            $response->header('Access-Control-Allow-Origin', $origin);
        } else {
            $response->header('Access-Control-Allow-Origin', $allowedOrigins[0]);
        }

        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key, X-CSRF-Token');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('Access-Control-Max-Age', '86400');

        return $response;
    }
}
