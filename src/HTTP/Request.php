<?php

declare(strict_types=1);

namespace GSMSDK\HTTP;

use GSMSDK\Contracts\RequestInterface;

/**
 * HTTP Request object
 *
 * Immutable representation of an incoming HTTP request with
 * convenient accessors for input data, headers, and metadata.
 */
class Request implements RequestInterface
{
    /** @var string HTTP method */
    private string $method;

    /** @var string Request URI */
    private string $uri;

    /** @var array<string, string> Request headers */
    private array $headers;

    /** @var array<string, mixed> Query parameters */
    private array $query;

    /** @var array<string, mixed> POST parameters */
    private array $post;

    /** @var array<string, mixed> Parsed JSON body */
    private array $json;

    /** @var array<string, mixed> Uploaded files */
    private array $files;

    /** @var array<string, mixed> Server parameters */
    private array $server;

    /** @var array<string, mixed> Cookie parameters */
    private array $cookies;

    /** @var string Raw request body */
    private string $content;

    /**
     * Create request from global PHP variables
     */
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
    }

    /**
     * Create request from custom data
     *
     * @param  array<string, mixed>  $data  Custom request data
     */
    public static function create(array $data): self
    {
        $request = new self();

        if (isset($data['method'])) {
            $request->method = strtoupper($data['method']);
        }
        if (isset($data['uri'])) {
            $request->uri = $data['uri'];
        }
        if (isset($data['headers'])) {
            $request->headers = $data['headers'];
        }
        if (isset($data['query'])) {
            $request->query = $data['query'];
        }
        if (isset($data['post'])) {
            $request->post = $data['post'];
        }
        if (isset($data['json'])) {
            $request->json = $data['json'];
        }
        if (isset($data['files'])) {
            $request->files = $data['files'];
        }
        if (isset($data['server'])) {
            $request->server = $data['server'];
        }
        if (isset($data['cookies'])) {
            $request->cookies = $data['cookies'];
        }
        if (isset($data['content'])) {
            $request->content = $data['content'];
        }

        return $request;
    }

    /**
     * Get HTTP method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get request path (URI without query string)
     */
    public function path(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return $path ?: '/';
    }

    /**
     * Check if request matches a method
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    /**
     * Check if request expects JSON response
     */
    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json');
    }

    /**
     * Get all input data (query + post + json merged)
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json);
    }

    /**
     * Get input value by key
     *
     * @param  string  $key  Dot-notation key
     * @param  mixed  $default  Default value if not found
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($all) || !array_key_exists($segment, $all)) {
                return $default;
            }
            $all = $all[$segment];
        }

        return $all;
    }

    /**
     * Get query parameter
     *
     * @param  string  $key  Parameter name
     * @param  mixed  $default  Default value
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get POST parameter
     *
     * @param  string  $key  Parameter name
     * @param  mixed  $default  Default value
     * @return mixed
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get JSON body parameter
     *
     * @param  string  $key  Parameter name
     * @param  mixed  $default  Default value
     * @return mixed
     */
    public function json(string $key, mixed $default = null): mixed
    {
        return $this->json[$key] ?? $default;
    }

    /**
     * Get uploaded file
     *
     * @param  string  $key  File input name
     * @return array<string, mixed>|null  File data
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get request header
     *
     * @param  string  $key  Header name (case-insensitive)
     * @param  string  $default  Default value
     * @return string
     */
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

    /**
     * Get all headers
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get server parameter
     */
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        return $this->server('HTTP_X_FORWARDED_FOR') ??
               $this->server('HTTP_CLIENT_IP') ??
               $this->server('REMOTE_ADDR') ??
               '0.0.0.0';
    }

    /**
     * Get user agent
     */
    public function userAgent(): string
    {
        return $this->server('HTTP_USER_AGENT', '');
    }

    /**
     * Get raw request body
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Check if request has input key
     *
     * @param  string  $key  Input key
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    /**
     * Extract all HTTP headers from server variables
     *
     * @return array<string, string>
     */
    private function extractHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerName] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$headerName] = $value;
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

    /**
     * Parse JSON request body
     *
     * @return array<string, mixed>
     */
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
