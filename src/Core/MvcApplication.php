<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Contracts\ContainerInterface;
use GSMSDK\Exceptions\ValidationException;
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
    /**
     * Registered routes
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Current request
     *
     * @var \GSMSDK\HTTP\Request|null
     */
    protected $request;

    /**
     * Register a GET route
     *
     * @param string $path
     * @param callable|string $handler
     * @return $this
     */
    public function routeGet(string $path, $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    /**
     * Register a POST route
     *
     * @param string $path
     * @param callable|string $handler
     * @return $this
     */
    public function routePost(string $path, $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    /**
     * Run the application
     *
     * @return void
     */
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $uri = strtok($uri, '?');

        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];
            $request = new \GSMSDK\HTTP\Request($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
            $response = new \GSMSDK\HTTP\Response();
            $this->request = $request;

            if (is_callable($handler)) {
                $result = $handler($request, $response);
                if ($result !== null && $response->getBody() === '') {
                    echo $result;
                } elseif ($response->getBody() !== '') {
                    echo $response->getBody();
                }
            } elseif (is_string($handler) && strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler);
                if (class_exists($controller) && method_exists($controller, $method)) {
                    $instance = new $controller($this);
                    $result = $instance->$method($request, $response);
                    if ($result !== null && $response->getBody() === '') {
                        echo $result;
                    } elseif ($response->getBody() !== '') {
                        echo $response->getBody();
                    }
                } else {
                    $response->status(404);
                    echo 'Controller or method not found';
                }
            } else {
                $response->status(404);
                echo 'Route not found';
            }
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }

    /**
     * Get current request
     *
     * @return \GSMSDK\HTTP\Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }
}
