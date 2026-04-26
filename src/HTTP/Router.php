<?php

declare(strict_types=1);

namespace GSMSDK\HTTP;

use GSMSDK\Core\Controller;
use GSMSDK\Exceptions\HttpException;
use GSMSDK\Exceptions\NotFoundException;

/**
 * Laravel 13-inspired Router with modern features
 *
 * Supports route groups, middleware, named routes, route caching,
 * model binding, and resourceful routing.
 */
class Router
{
    /** @var array<string, array> Registered routes */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
        'OPTIONS' => [],
    ];

    /** @var array<string, array> Route groups */
    private array $groups = [];

    /** @var array<string, string> Named routes */
    private array $namedRoutes = [];

    /** @var array<string, mixed> Route middleware */
    private array $middleware = [];

    /** @var array<string, callable> Route model bindings */
    private array $modelBindings = [];

    /** @var array Cached routes */
    private array $cachedRoutes = [];

    /** @var bool Whether routes are cached */
    private bool $isCached = false;

    /** @var array Global middleware stack */
    private array $globalMiddleware = [];

    /**
     * Register a GET route
     */
    public function get(string $uri, callable|string $handler, string $name = ''): self
    {
        return $this->addRoute('GET', $uri, $handler, $name);
    }

    /**
     * Register a POST route
     */
    public function post(string $uri, callable|string $handler, string $name = ''): self
    {
        return $this->addRoute('POST', $uri, $handler, $name);
    }

    /**
     * Register a PUT route
     */
    public function put(string $uri, callable|string $handler, string $name = ''): self
    {
        return $this->addRoute('PUT', $uri, $handler, $name);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $uri, callable|string $handler, string $name = ''): self
    {
        return $this->addRoute('PATCH', $uri, $handler, $name);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $uri, callable|string $handler, string $name = ''): self
    {
        return $this->addRoute('DELETE', $uri, $handler, $name);
    }

    /**
     * Register routes for multiple methods
     */
    public function match(array $methods, string $uri, callable|string $handler, string $name = ''): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $uri, $handler, $name);
        }
        return $this;
    }

    /**
     * Register a route that responds to any HTTP method
     */
    public function any(string $uri, callable|string $handler, string $name = ''): self
    {
        foreach (array_keys($this->routes) as $method) {
            $this->addRoute($method, $uri, $handler, $name);
        }
        return $this;
    }

    /**
     * Register a redirect route
     */
    public function redirect(string $uri, string $destination, int $status = 302): self
    {
        $this->get($uri, function () use ($destination, $status) {
            header("Location: {$destination}", true, $status);
            exit;
        });
        return $this;
    }

    /**
     * Register a view route (returns a view directly)
     */
    public function view(string $uri, string $view, array $data = [], string $name = ''): self
    {
        $this->get($uri, function () use ($view, $data) {
            return $this->renderView($view, $data);
        }, $name);
        return $this;
    }

    /**
     * Register a resource controller (Laravel-style)
     */
    public function resource(string $uri, string $controller, array $options = []): self
    {
        $methods = [
            'index' => ['GET', ''],
            'create' => ['GET', '/create'],
            'store' => ['POST', ''],
            'show' => ['GET', '/{id}'],
            'edit' => ['GET', '/{id}/edit'],
            'update' => ['PUT/PATCH', '/{id}'],
            'destroy' => ['DELETE', '/{id}'],
        ];

        foreach ($methods as $action => [$method, $path]) {
            // Skip if action is excluded
            if (isset($options['except']) && in_array($action, (array) $options['except'])) {
                continue;
            }

            // Skip if only certain actions are included
            if (isset($options['only']) && !in_array($action, (array) $options['only'])) {
                continue;
            }

            $handler = $controller . '@' . $action;

            if (str_contains($method, '/')) {
                $this->match(explode('/', $method), $uri . $path, $handler, "{$uri}.{$action}");
            } else {
                $this->addRoute($method, $uri . $path, $handler, "{$uri}.{$action}");
            }
        }

        return $this;
    }

    /**
     * Register API resource (without create/edit)
     */
    public function apiResource(string $uri, string $controller, array $options = []): self
    {
        return $this->resource($uri, $controller, array_merge($options, [
            'except' => ['create', 'edit'],
        ]));
    }

    /**
     * Define a route group
     */
    public function group(array $attributes, callable $callback): self
    {
        $this->groups[] = $attributes;
        $callback($this);
        array_pop($this->groups);
        return $this;
    }

    /**
     * Add middleware to routes
     */
    public function middleware(string|array $middleware): self
    {
        $lastGroup = count($this->groups) - 1;
        if ($lastGroup >= 0) {
            if (!isset($this->groups[$lastGroup]['middleware'])) {
                $this->groups[$lastGroup]['middleware'] = [];
            }
            $this->groups[$lastGroup]['middleware'] = array_merge(
                (array) $this->groups[$lastGroup]['middleware'],
                (array) $middleware
            );
        } else {
            $this->middleware = array_merge($this->middleware, (array) $middleware);
        }
        return $this;
    }

    /**
     * Add prefix to routes
     */
    public function prefix(string $prefix): self
    {
        $lastGroup = count($this->groups) - 1;
        if ($lastGroup >= 0) {
            $this->groups[$lastGroup]['prefix'] = $prefix;
        }
        return $this;
    }

    /**
     * Add name prefix to routes
     */
    public function name(string $name): self
    {
        $lastGroup = count($this->groups) - 1;
        if ($lastGroup >= 0) {
            $this->groups[$lastGroup]['name'] = $name;
        }
        return $this;
    }

    /**
     * Add namespace to routes
     */
    public function namespace(string $namespace): self
    {
        $lastGroup = count($this->groups) - 1;
        if ($lastGroup >= 0) {
            $this->groups[$lastGroup]['namespace'] = $namespace;
        }
        return $this;
    }

    /**
     * Bind a model to a route parameter
     */
    public function model(string $parameter, string $class, callable $resolver = null): self
    {
        $this->modelBindings[$parameter] = [
            'class' => $class,
            'resolver' => $resolver,
        ];
        return $this;
    }

    /**
     * Add global middleware
     */
    public function addGlobalMiddleware(string $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    /**
     * Add a route to the collection
     */
    private function addRoute(string $method, string $uri, callable|string $handler, string $name = ''): self
    {
        // Apply group attributes
        $uri = $this->applyGroupPrefix($uri);
        $handler = $this->applyGroupNamespace($handler);
        $name = $this->applyGroupName($name);
        $middleware = $this->getCurrentMiddleware();

        $route = [
            'uri' => $uri,
            'handler' => $handler,
            'name' => $name,
            'middleware' => $middleware,
            'parameter_patterns' => [],
        ];

        $this->routes[$method][$this->normalizeUri($uri)] = $route;

        // Register named route
        if ($name) {
            $this->namedRoutes[$name] = $uri;
        }

        return $this;
    }

    /**
     * Dispatch the request to the appropriate handler
     */
    public function dispatch(string $method, string $uri, Request $request): Response
    {
        // Apply global middleware
        foreach ($this->globalMiddleware as $middleware) {
            $result = $this->executeMiddleware($middleware, $request);
            if ($result instanceof Response) {
                return $result;
            }
        }

        $method = strtoupper($method);
        $normalizedUri = $this->normalizeUri($uri);

        // Check for exact match first
        if (isset($this->routes[$method][$normalizedUri])) {
            return $this->executeRoute($this->routes[$method][$normalizedUri], $request);
        }

        // Check for pattern match
        foreach ($this->routes[$method] as $route) {
            if ($params = $this->matchPattern($route['uri'], $normalizedUri)) {
                $request->setRouteParams($params);
                return $this->executeRoute($route, $request);
            }
        }

        // Check for method override (e.g., POST with _method=PUT)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $overrideMethod = strtoupper($_POST['_method']);
            if (isset($this->routes[$overrideMethod])) {
                foreach ($this->routes[$overrideMethod] as $route) {
                    if ($params = $this->matchPattern($route['uri'], $normalizedUri)) {
                        $request->setRouteParams($params);
                        return $this->executeRoute($route, $request);
                    }
                }
            }
        }

        throw new NotFoundException("Route [{$method}] {$uri} not found");
    }

    /**
     * Execute a route handler
     */
    private function executeRoute(array $route, Request $request): Response
    {
        // Execute route middleware
        foreach ($route['middleware'] as $middleware) {
            $result = $this->executeMiddleware($middleware, $request);
            if ($result instanceof Response) {
                return $result;
            }
        }

        $handler = $route['handler'];

        // Resolve model bindings
        $params = $request->getRouteParams();
        foreach ($params as $key => $value) {
            if (isset($this->modelBindings[$key])) {
                $params[$key] = $this->resolveModelBinding($key, $value);
            }
        }

        if (is_callable($handler)) {
            return $this->callHandler($handler, $params, $request);
        }

        return $this->callControllerAction($handler, $params, $request);
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware(string $middleware, Request $request): ?Response
    {
        $middlewareClass = "App\\Middleware\\{$middleware}";

        if (class_exists($middlewareClass)) {
            $instance = new $middlewareClass();
            return $instance->handle($request);
        }

        return null;
    }

    /**
     * Resolve model binding
     */
    private function resolveModelBinding(string $parameter, $value)
    {
        if (!isset($this->modelBindings[$parameter])) {
            return $value;
        }

        $binding = $this->modelBindings[$parameter];

        if ($binding['resolver']) {
            return ($binding['resolver'])($value);
        }

        // Default resolver
        $class = $binding['class'];
        if (class_exists($class) && method_exists($class, 'find')) {
            return $class::find($value);
        }

        return $value;
    }

    /**
     * Call a handler function
     */
    private function callHandler(callable $handler, array $params, Request $request): Response
    {
        try {
            $response = $handler($request, ...array_values($params));

            if ($response instanceof Response) {
                return $response;
            }

            return new Response((string) $response);
        } catch (\Throwable $e) {
            throw new HttpException($e->getMessage(), 500, $e);
        }
    }

    /**
     * Call a controller action
     */
    private function callControllerAction(string $handler, array $params, Request $request): Response
    {
        if (!str_contains($handler, '@')) {
            throw new HttpException("Invalid controller action: {$handler}");
        }

        [$controllerClass, $method] = explode('@', $handler, 2);

        if (!class_exists($controllerClass)) {
            throw new HttpException("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass($this->getApplication());

        if (!method_exists($controller, $method)) {
            throw new HttpException("Method [{$method}] not found on controller [{$controllerClass}]");
        }

        try {
            $response = $controller->{$method}($request, ...array_values($params));

            if ($response instanceof Response) {
                return $response;
            }

            if (is_array($response)) {
                return Response::json($response);
            }

            return new Response((string) $response);
        } catch (\Throwable $e) {
            throw new HttpException($e->getMessage(), 500, $e);
        }
    }

    /**
     * Render a view
     */
    private function renderView(string $view, array $data): Response
    {
        $app = $this->getApplication();
        $html = $app->view($view, $data);
        return new Response($html);
    }

    /**
     * Get application instance
     */
    private function getApplication(): \GSMSDK\Core\Application
    {
        global $app;
        return $app ?? new \GSMSDK\Core\Application([]);
    }

    /**
     * Match a pattern against the URI
     *
     * Supports: {param}, {param?}, {param:\d+}
     */
    private function matchPattern(string $pattern, string $uri): ?array
    {
        $pattern = preg_quote($pattern, '#');

        // Convert route parameters to regex
        $pattern = preg_replace_callback(
            '/\\\{([^}]+)\\\}/',
            function ($matches) {
                $param = $matches[1];
                $optional = false;
                $regex = '[^/]+';

                // Check if optional
                if (str_ends_with($param, '?')) {
                    $optional = true;
                    $param = substr($param, 0, -1);
                }

                // Check for custom regex
                if (str_contains($param, ':')) {
                    [$param, $regex] = explode(':', $param, 2);
                }

                $pattern = '(' . $regex . ')';
                return $optional ? "(?:{$pattern})?" : $pattern;
            },
            $pattern
        );

        $pattern = '#^' . $pattern . '$#u';

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);

            // Get parameter names
            preg_match_all('/\\\{([^}]+)\\\}/', $pattern, $paramMatches);
            $paramNames = [];
            foreach ($paramMatches[1] as $param) {
                if (str_contains($param, '?')) {
                    $param = substr($param, 0, -1);
                }
                if (str_contains($param, ':')) {
                    $param = explode(':', $param)[0];
                }
                $paramNames[] = $param;
            }

            return array_combine($paramNames, $matches);
        }

        return null;
    }

    /**
     * Apply group prefix to URI
     */
    private function applyGroupPrefix(string $uri): string
    {
        $prefix = '';
        foreach ($this->groups as $group) {
            if (isset($group['prefix'])) {
                $prefix .= $group['prefix'];
            }
        }
        return $prefix . $uri;
    }

    /**
     * Apply group namespace to handler
     */
    private function applyGroupNamespace(string $handler): string
    {
        if (is_callable($handler)) {
            return $handler;
        }

        $namespace = '';
        foreach ($this->groups as $group) {
            if (isset($group['namespace'])) {
                $namespace .= $group['namespace'] . '\\';
            }
        }

        if ($namespace && !str_starts_with($handler, 'App\\')) {
            $handler = $namespace . $handler;
        }

        return $handler;
    }

    /**
     * Apply group name prefix
     */
    private function applyGroupName(string $name): string
    {
        if (!$name) {
            return $name;
        }

        $namePrefix = '';
        foreach ($this->groups as $group) {
            if (isset($group['name'])) {
                $namePrefix .= $group['name'] . '.';
            }
        }

        return $namePrefix . $name;
    }

    /**
     * Get current middleware stack
     */
    private function getCurrentMiddleware(): array
    {
        $middleware = $this->middleware;

        foreach ($this->groups as $group) {
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array) $group['middleware']);
            }
        }

        return $middleware;
    }

    /**
     * Normalize URI
     */
    private function normalizeUri(string $uri): string
    {
        return trim($uri, '/') ?: '/';
    }

    /**
     * Generate URL for a named route
     */
    public function route(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route [{$name}] not defined");
        }

        $uri = $this->namedRoutes[$name];

        foreach ($parameters as $key => $value) {
            $uri = str_replace("{{$key}}", (string) $value, $uri);
        }

        return '/' . ltrim($uri, '/');
    }

    /**
     * Cache routes for better performance
     */
    public function cache(string $path): bool
    {
        $cache = [
            'routes' => $this->routes,
            'namedRoutes' => $this->namedRoutes,
        ];

        return file_put_contents($path, '<?php return ' . var_export($cache, true) . ';') !== false;
    }

    /**
     * Load cached routes
     */
    public function loadCache(string $path): bool
    {
        if (file_exists($path)) {
            $cache = require $path;
            $this->routes = $cache['routes'] ?? [];
            $this->namedRoutes = $cache['namedRoutes'] ?? [];
            $this->isCached = true;
            return true;
        }

        return false;
    }

    /**
     * Get all routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get named routes
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }
}
