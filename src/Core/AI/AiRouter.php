<?php

declare(strict_types=1);

namespace GSMSDK\Core\AI;

use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;
use GSMSDK\Core\Application;

/**
 * AI-Powered Smart Router
 *
 * Automatically routes requests based on intent,
 * provides intelligent API suggestions,
 * and generates API documentation.
 */
class AiRouter
{
    /** @var Application */
    private Application $app;

    /** @var array<string, mixed> Route definitions */
    private array $routes = [];

    /** @var array<string, mixed> Intent mappings */
    private array $intents = [];

    /** @var array<string, mixed> API documentation */
    private array $apiDocs = [];

    /** @var array<string, mixed> ML model cache */
    private array $modelCache = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register a route with AI metadata
     */
    public function register(string $method, string $path, callable $handler, array $meta = []): self
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'meta' => array_merge([
                'description' => '',
                'tags' => [],
                'auth' => false,
                'throttle' => true,
                'cache' => false,
                'cache_ttl' => 300,
                'validation' => [],
                'response_codes' => [200 => 'Success'],
                'examples' => [],
            ], $meta),
        ];

        $this->generateIntent($method, $path, $meta);
        $this->generateApiDoc($method, $path, $meta);

        return $this;
    }

    /**
     * Generate intent pattern for AI matching
     */
    private function generateIntent(string $method, string $path, array $meta): void
    {
        $pathParts = explode('/', trim($path, '/'));
        $patterns = [];

        // Generate various intent patterns
        $patterns[] = strtolower($method) . ' ' . implode(' ', $pathParts);
        $patterns[] = implode(' ', array_map(function ($p) {
            return str_replace(['{', '}', 'id', ':'], ['', '', '', ''], $p);
        }, $pathParts));

        if (!empty($meta['description'])) {
            $patterns[] = $this->extractKeywords($meta['description']);
        }

        $intentId = md5($method . $path);
        $this->intents[$intentId] = [
            'patterns' => $patterns,
            'path' => $path,
            'method' => $method,
            'confidence_threshold' => 0.7,
        ];
    }

    /**
     * Extract keywords from description
     */
    private function extractKeywords(string $text): string
    {
        $stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $words = preg_split('/\s+/', strtolower($text));
        return implode(' ', array_diff($words, $stopWords));
    }

    /**
     * Generate API documentation
     */
    private function generateApiDoc(string $method, string $path, array $meta): void
    {
        $docKey = strtoupper($method) . ' ' . $path;
        $this->apiDocs[$docKey] = [
            'method' => strtoupper($method),
            'path' => $path,
            'summary' => $meta['description'] ?? '',
            'tags' => $meta['tags'] ?? [],
            'security' => $meta['auth'] ? [['bearerAuth' => []]] : [],
            'parameters' => $this->extractParameters($path),
            'requestBody' => $meta['validation'] ?? null,
            'responses' => $meta['response_codes'] ?? [],
            'examples' => $meta['examples'] ?? [],
            'cacheable' => $meta['cache'] ?? false,
        ];
    }

    /**
     * Extract parameters from path
     */
    private function extractParameters(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        $params = [];

        foreach ($matches[1] as $param) {
            $parts = explode(':', $param);
            $name = $parts[0];
            $pattern = $parts[1] ?? '[^/]+';

            $params[] = [
                'name' => $name,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => $this->inferType($pattern)],
                'pattern' => $pattern,
            ];
        }

        return $params;
    }

    /**
     * Infer type from pattern
     */
    private function inferType(string $pattern): string
    {
        if (str_contains($pattern, 'd')) return 'integer';
        if (str_contains($pattern, 'w')) return 'string';
        return 'string';
    }

    /**
     * Match request using AI intent analysis
     */
    public function match(Request $request): ?array
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Exact match first
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchesPattern($route['path'], $uri)) {
                return $route;
            }
        }

        // AI-powered fuzzy matching
        return $this->fuzzyMatch($method, $uri);
    }

    /**
     * Check if path matches pattern
     */
    private function matchesPattern(string $pattern, string $uri): bool
    {
        $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        return (bool) preg_match($regex, $uri);
    }

    /**
     * Fuzzy match using intent analysis
     */
    private function fuzzyMatch(string $method, string $uri): ?array
    {
        $intent = $this->analyzeIntent($method, $uri);
        $bestMatch = null;
        $bestConfidence = 0;

        foreach ($this->intents as $intentData) {
            $confidence = $this->calculateConfidence($intent, $intentData['patterns']);

            if ($confidence > $bestConfidence && $confidence >= $intentData['confidence_threshold']) {
                $bestConfidence = $confidence;
                $bestMatch = $this->findRoute($intentData['method'], $intentData['path']);
            }
        }

        return $bestMatch;
    }

    /**
     * Analyze request intent
     */
    private function analyzeIntent(string $method, string $uri): string
    {
        $pathParts = explode('/', trim($uri, '/'));
        $intent = strtolower($method) . ' ' . implode(' ', $pathParts);

        return $intent;
    }

    /**
     * Calculate confidence score
     */
    private function calculateConfidence(string $intent, array $patterns): float
    {
        $maxScore = 0;

        foreach ($patterns as $pattern) {
            similar_text($intent, $pattern, $score);
            $maxScore = max($maxScore, $score / 100);
        }

        return $maxScore;
    }

    /**
     * Find route by method and path
     */
    private function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && $route['path'] === $path) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Smart middleware that adapts based on request
     */
    public function smartMiddleware(Request $request): ?Response
    {
        // Apply rate limiting intelligently
        if ($this->shouldApplyRateLimit($request)) {
            $result = $this->app->auth->rateLimitMiddleware($request, 'api');
            if ($result) return $result;
        }

        // Verify authentication if needed
        if ($this->requiresAuth($request)) {
            $result = $this->app->auth->authMiddleware($request);
            if ($result) return $result;
        }

        return null;
    }

    /**
     * Determine if rate limiting should apply
     */
    private function shouldApplyRateLimit(Request $request): bool
    {
        $excludedPaths = ['/health', '/status', '/login'];
        return !in_array($request->getUri(), $excludedPaths);
    }

    /**
     * Determine if authentication is required
     */
    private function requiresAuth(Request $request): bool
    {
        foreach ($this->routes as $route) {
            if ($this->matchesPattern($route['path'], $request->getUri())) {
                return $route['meta']['auth'] ?? false;
            }
        }
        return false;
    }

    /**
     * Generate OpenAPI/Swagger documentation
     */
    public function generateOpenApiSpec(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'GSMSDK API',
                'version' => '2.0.0',
                'description' => 'AI-powered API for Android device management',
            ],
            'servers' => [
                ['url' => 'http://localhost:8000'],
            ],
            'paths' => $this->generateOpenApiPaths(),
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate OpenAPI paths
     */
    private function generateOpenApiPaths(): array
    {
        $paths = [];

        foreach ($this->apiDocs as $doc) {
            $path = $doc['path'];
            $method = strtolower($doc['method']);

            $paths[$path][$method] = [
                'summary' => $doc['summary'],
                'tags' => $doc['tags'],
                'parameters' => $doc['parameters'],
                'responses' => [],
            ];

            foreach ($doc['responses'] as $code => $description) {
                $paths[$path][$method]['responses'][$code] = [
                    'description' => $description,
                ];
            }

            if ($doc['requestBody']) {
                $paths[$path][$method]['requestBody'] = [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => $this->convertValidationToSchema($doc['requestBody']),
                            ],
                        ],
                    ],
                ];
            }
        }

        return $paths;
    }

    /**
     * Convert validation rules to OpenAPI schema
     */
    private function convertValidationToSchema(array $rules): array
    {
        $schema = [];

        foreach ($rules as $field => $ruleString) {
            $type = 'string';
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                if ($rule === 'integer' || $rule === 'numeric') {
                    $type = 'integer';
                } elseif ($rule === 'array') {
                    $type = 'array';
                } elseif ($rule === 'boolean') {
                    $type = 'boolean';
                }
            }

            $schema[$field] = ['type' => $type];
        }

        return $schema;
    }

    /**
     * Get all API routes
     */
    public function getApiRoutes(): array
    {
        return $this->apiDocs;
    }

    /**
     * Validate request using AI rules
     */
    public function validateRequest(Request $request, string $path): array
    {
        foreach ($this->routes as $route) {
            if ($this->matchesPattern($route['path'], $path)) {
                $validation = $route['meta']['validation'] ?? [];

                if (!empty($validation)) {
                    return $request->validate($validation);
                }

                break;
            }
        }

        return $request->all();
    }

    /**
     * Get route metadata
     */
    public function getRouteMeta(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && $route['path'] === $path) {
                return $route['meta'];
            }
        }
        return null;
    }

    /**
     * Check if route exists
     */
    public function hasRoute(string $method, string $path): bool
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && $route['path'] === $path) {
                return true;
            }
        }
        return false;
    }
}
