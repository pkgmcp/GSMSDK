<?php

declare(strict_types=1);

namespace GSMSDK\Core\Api;

use GSMSDK\Core\Application;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;
use GSMSDK\Middleware\ApiMiddleware;

/**
 * API Application - Handles API requests with AI-powered routing
 */
class ApiApplication
{
    /** @var Application */
    private Application $app;

    /** @var array<string, mixed> Middleware stack */
    private array $middleware = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Add middleware
     */
    public function addMiddleware(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Run API application
     */
    public function run(): void
    {
        $request = new Request();

        // Set JSON headers
        header('Content-Type: application/json; charset=utf-8');
        header('X-Powered-By: GSMSDK/' . $this->app->version());
        header('X-API-Version: 2.0.0');

        // Apply CORS
        $corsResponse = ApiMiddleware::addCorsHeaders($request);
        foreach ($corsResponse->getHeaders() as $name => $value) {
            header("{$name}: {$value}");");
        }

        // Handle preflight
        if ($request->getMethod() === 'OPTIONS') {
            http_response_code(200);
            echo json_encode(['status' => 'ok']);
            return;
        }

        // Log request
        ApiMiddleware::logRequest($request);

        // Rate limiting
        if ($this->app->config('api.throttle', true)) {
            $result = ApiMiddleware::throttle($request);
            if ($result) {
                $result->send();
                return;
            }
        }

        // Validate content type for POST/PUT/PATCH
        $result = ApiMiddleware::validateContentType($request);
        if ($result) {
            $result->send();
            return;
        }

        try {
            // Load API routes
            $routes = $this->loadRoutes();

            // Match and dispatch
            $response = $this->dispatch($request, $routes);
            $response->send();

        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Load API routes
     * @return array<string, mixed>
     */
    private function loadRoutes(): array
    {
        $routes = [];
        $routeFiles = glob($this->app->basePath() . '/routes/api*.php');

        foreach ($routeFiles as $file) {
            $router = new \GSMSDK\HTTP\Router();
            require $file;

            if (is_callable($callback)) {
                $callback($router);
            }

            foreach ($router->getRoutes() as $method => $methodRoutes) {
                foreach ($methodRoutes as $route) {
                    $routes[strtoupper($method)][$route['uri']] = $route;
                }
            }
        }

        return $routes;
    }

    /**
     * Dispatch request to matching route
     */
    private function dispatch(Request $request, array $routes): Response
    {
        $method = $request->getMethod();
        $uri = $this->normalizeUri($request->getUri());

        // Exact match
        if (isset($routes[$method][$uri])) {
            return $this->executeRoute($routes[$method][$uri], $request);
        }

        // Pattern match
        foreach ($routes[$method] ?? [] as $route) {
            if ($params = $this->matchPattern($route['uri'], $uri)) {
                $request->setRouteParams($params);
                return $this->executeRoute($route, $request);
            }
        }

        return Response::json(['error' => 'Endpoint not found', 'path' => $uri], 404);
    }

    /**
     * Execute route handler
     */
    private function executeRoute(array $route, Request $request): Response
    {
        // Check authentication if required
        if ($route['meta']['auth'] ?? false) {
            if (!$this->app->auth->check()) {
                return Response::json(['error' => 'Unauthorized'], 401);
            }
        }

        // Execute middleware
        if (!empty($route['middleware'])) {
            foreach ($route['middleware'] as $middleware) {
                $result = $this->executeMiddleware($middleware, $request);
                if ($result instanceof Response) {
                    return $result;
                }
            }
        }

        // Validate request if rules defined
        if (!empty($route['meta']['validation'])) {
            try {
                $request->validate($route['meta']['validation']);
            } catch (\Exception $e) {
                return Response::json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage(), true)], 422);
            }
        }

        // Execute handler
        $handler = $route['handler'];
        $params = $request->getRouteParams();

        try {
            if (is_callable($handler)) {
                $response = $handler($request, ...array_values($params));
            } elseif (is_string($handler) && str_contains($handler, '@')) {
                [$controllerClass, $method] = explode('@', $handler, 2);

                if (!class_exists($controllerClass)) {
                    throw new \Exception("Controller not found: {$controllerClass}");
                }

                $controller = $this->app->make($controllerClass);

                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method {$method} not found on {$controllerClass}");
                }

                $response = $controller->{$method}($request, ...array_values($params));
            } else {
                throw new \Exception("Invalid route handler");
            }

            if ($response instanceof Response) {
                return $response;
            }

            return Response::json($response);

        } catch (\Exception $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            return Response::json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware(string $middleware, Request $request): ?Response
    {
        $middlewareClass = "GSMSDK\\Middleware\\{$middleware}";

        if (class_exists($middlewareClass) && method_exists($middlewareClass, 'handle')) {
            return $middlewareClass::handle($request);
        }

        return null;
    }

    /**
     * Match route pattern with parameters
     * @return ?array<string, string>
     */
    private function matchPattern(string $pattern, string $uri): ?array
    {
        $regex = preg_replace_callback('/\{([^}]+)\}/', function ($matches) {
            $param = $matches[1];
            if (str_contains($param, ':')) {
                $param = explode(':', $param)[0];
            }
            if (str_ends_with($param, '?')) {
                $param = substr($param, 0, -1);
                return '(?P<' . $param . '>[^/]*)?';
            }
            return '(?P<' . $param . '>[^/]+)';
        }, $pattern);

        $regex = '#^' . str_replace('/', '\\/', $regex) . '$#';

        if (preg_match($regex, $uri, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }

        return null;
    }

    /**
     * Normalize URI
     */
    private function normalizeUri(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Handle exception
     */
    private function handleException(\Exception $e): void
    {
        error_log('[API Exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
        $response = Response::json(['error' => 'Internal server error'], $code);
        $response->send();
    }
}
