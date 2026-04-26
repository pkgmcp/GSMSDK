<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Contracts\ContainerInterface;
use GSMSDK\Exceptions\\ValidationException;
use GSMSDK\Traits\Configurable;

/**
 * GSMSDK MVC Application
 *
 * Full MVC application framework with routing, controllers,
 * views, models, and middleware support.
 *
 * @implements ContainerInterface<mixed>
 */
class MvcApplication extends Application
{
    use Configurable;

    /** @var array<string, callable> Route definitions */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    /** @var array<string, callable> Middleware stack */
    private array $middleware = [];

    /** @var string Base path for views */
    private string $viewPath;

    /** @var string Base path for controllers */
    private string $controllerPath;

    /** @var string Base path for models */
    private string $modelPath;

    /** @var string Default controller namespace */
    private string $namespace = 'App\\Controllers';

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $basePath = $this->config('paths.base', dirname(__DIR__, 2));

        $this->viewPath = $config['paths']['views'] ?? $basePath . '/resources/views';
        $this->controllerPath = $config['paths']['controllers'] ?? $basePath . '/app/Controllers';
        $this->modelPath = $config['paths']['models'] ?? $basePath . '/app/Models';

        $this->setupPaths();
    }

    /**
     * Setup required directories
     */
    private function setupPaths(): void
    {
        foreach ([$this->viewPath, $this->controllerPath, $this->modelPath] as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Register a GET route
     */
    public function get(string $path, callable|string $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable|string $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, callable|string $handler): self
    {
        $this->routes['PUT'][$path] = $handler;
        return $this;
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, callable|string $handler): self
    {
        $this->routes['DELETE'][$path] = $handler;
        return $this;
    }

    /**
     * Register middleware
     */
    public function addMiddleware(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Set controller namespace
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $request = $this->make('request');
        $response = $this->make('response');

        try {
            // Apply middleware
            $this->applyMiddleware($request, $response);

            // Route the request
            $this->route($request, $response);
        } catch (\Throwable $e) {
            $this->handleException($e, $response);
        }

        $response->send();
    }

    /**
     * Apply middleware stack
     */
    private function applyMiddleware($request, $response): void
    {
        foreach ($this->middleware as $middleware) {
            $result = $middleware($request, $response);
            if ($result === false) {
                throw new \RuntimeException('Middleware blocked request');
            }
        }
    }

    /**
     * Route the request to handler
     */
    private function route($request, $response): void
    {
        $method = $request->method();
        $path = $request->path();

        // Check exact match first
        if (isset($this->routes[$method][$path])) {
            $this->executeHandler($this->routes[$method][$path], $request, $response);
            return;
        }

        // Check pattern matches
        foreach ($this->routes[$method] as $route => $handler) {
            if ($this->matchPattern($route, $path, $matches)) {
                $request->setRouteParams($matches);
                $this->executeHandler($handler, $request, $response);
                return;
            }
        }

        // Not found
        $response->status(404)->json(['error' => 'Not found']);
    }

    /**
     * Match route pattern with parameters
     */
    private function matchPattern(string $pattern, string $path, &$matches = []): bool
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $path, $matches) === 1;
    }

    /**
     * Execute route handler
     */
    private function executeHandler(callable|string $handler, $request, $response): void
    {
        if (is_callable($handler)) {
            $handler($request, $response);
            return;
        }

        if (is_string($handler)) {
            $this->executeControllerAction($handler, $request, $response);
            return;
        }

        throw new \InvalidArgumentException('Invalid route handler');
    }

    /**
     * Execute controller action
     */
    private function executeControllerAction(string $handler, $request, $response): void
    {
        if (str_contains($handler, '@')) {
            [$controllerName, $action] = explode('@', $handler);
        } else {
            $controllerName = $handler;
            $action = 'index';
        }

        $controllerClass = $this->namespace . '\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass($this);

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action not found: {$action}");
        }

        $controller->$action($request, $response);
    }

    /**
     * Render view
     */
    public function view(string $name, array $data = []): string
    {
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $name) . '.gsm.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$name}");
        }

        extract($data);
        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Render view with layout
     */
    public function render(string $view, array $data = [], ?string $layout = 'main'): string
    {
        $content = $this->view($view, $data);

        if ($layout === null) {
            return $content;
        }

        return $this->view('layouts/' . $layout, array_merge($data, ['content' => $content]));
    }

    /**
     * Create model instance
     */
    public function model(string $name, array $attributes = [])
    {
        $modelClass = $this->namespace . '\\Models\\' . $name;

        if (!class_exists($modelClass)) {
            throw new \RuntimeException("Model not found: {$modelClass}");
        }

        return new $modelClass($attributes);
    }

    /**
     * Get asset URL
     */
    public function asset(string $path): string
    {
        $baseUrl = $this->config('app.url', '/');
        return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }
}
