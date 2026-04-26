<?php

declare(strict_types=1);

namespace GSMSDK\HTTP;

/**
 * HTTP Request object
 *
 * Enhanced with route parameters and session support
 */
class Request implements RequestInterface
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $query;
    private array $post;
    private array $json;
    private array $files;
    private array $server;
    private array $cookies;
    private string $content;
    /** @var array<string, mixed> Route parameters */
    private array $routeParams = [];
    /** @var array<string, mixed> Session data */
    private array $session = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->headers = $this->extractHeaders();
        $this->query = $_GET;
        $this->post = $_POST;
        $this->json = $this->parseJsonBody();
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->content = file_get_contents('php://input') ?: '';
        $this->session = $_SESSION ?? [];
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return $path ?: '/';
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json');
    }

    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function json(string $key, mixed $default = null): mixed
    {
        return $this->json[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, string $default = ''): string
    {
        $normalizedKey = strtolower($key);
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === $normalizedKey) {
                return $value;
            }
        }
        return $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        return $this->server('HTTP_X_FORWARDED_FOR') ??
               $this->server('HTTP_CLIENT_IP') ??
               $this->server('REMOTE_ADDR') ??
               '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server('HTTP_USER_AGENT', '');
    }

    public function content(): string
    {
        return $this->content;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    /**
     * Get route parameter
     */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Get all route parameters
     *
     * @return array<string, mixed>
     */
    public function routeParams(): array
    {
        return $this->routeParams;
    }

    /**
     * Set route parameters
     *
     * @param  array<string, mixed>  $params  Route parameters
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * Get session value
     */
    public function session(string $key, mixed $default = null): mixed
    {
        return $this->session[$key] ?? $default;
    }

    /**
     * Get CSRF token
     */
    public function csrfToken(): string
    {
        return $this->session('_token') ?? '';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With')) === 'xmlhttprequest';
    }

    private function extractHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$name] = $value;
            }
        }
        if (isset($this->server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $this->server['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = $this->server['PHP_AUTH_PW'] ?? '';
        } elseif (isset($this->server['HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = $this->server['HTTP_AUTHORIZATION'];
        }
        return $headers;
    }

    private function parseJsonBody(): array
    {
        $contentType = $this->server('CONTENT_TYPE', '');
        if (str_contains($contentType, 'application/json') && $this->content !== '') {
            $decoded = json_decode($this->content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [];
            }
        }
        return [];
    }
}
