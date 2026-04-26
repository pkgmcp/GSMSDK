<?php

declare(strict_types=1);

namespace GSMSDK\HTTP;

/**
 * HTTP Request object with Laravel 13-inspired features
 */
class Request
{
    private array $get = [];
    private array $post = [];
    private array $server = [];
    private array $files = [];
    private array $cookies = [];
    private array $headers = [];
    private string $method;
    private string $uri;
    private ?string $body = null;
    private array $routeParams = [];
    private array $attributes = [];

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER["REQUEST_URI"] ?? "/";
        $this->parseHeaders();
    }

    private function parseHeaders(): void
    {
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $this->headers[$header] = $value;
            }
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function expectsJson(): bool
    {
        return str_contains($this->header('Accept', ''), 'application/json');
    }

    public function ajax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function has(string $key): bool
    {
        return isset($this->get[$key]) || isset($this->post[$key]);
    }

    public function filled(string $key): bool
    {
        $value = $this->all()[$key] ?? null;
        return !empty($value);
    }

    public function missing(string $key): bool
    {
        return !$this->filled($key);
    }

    public function body(): string
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input');
        }
        return $this->body;
    }

    public function json(string $key = null, mixed $default = null): mixed
    {
        static $data = null;
        if ($data === null) {
            $body = $this->body();
            $data = $body ? json_decode($body, true) : [];
        }
        if ($key === null) {
            return $data;
        }
        return $data[$key] ?? $default;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function setRouteParams(array $params): self
    {
        $this->routeParams = $params;
        return $this;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function isContentType(string $type): bool
    {
        return str_contains($this->header('Content-Type', ''), $type);
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function validate(array $rules): array
    {
        $data = $this->all();
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($rules as $rule) {
                if (str_contains($rule, ':')) {
                    [$ruleName, $ruleValue] = explode(':', $rule, 2);
                } else {
                    $ruleName = $rule;
                    $ruleValue = null;
                }

                $isValid = $this->validateRule($field, $value, $ruleName, $ruleValue, $data);
                if (!$isValid) {
                    $errors[$field][] = "The {$field} field {$this->getErrorMessage($ruleName)}.";
                }
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        return $data;
    }

    private function validateRule(string $field, mixed $value, string $rule, string $ruleValue, array $data): bool
    {
        return match ($rule) {
            'required' => !empty($value) || $value === 0,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'numeric' => is_numeric($value),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'string' => is_string($value),
            'array' => is_array($value),
            'min' => is_numeric($value) && $value >= $ruleValue,
            'max' => is_numeric($value) && $value <= $ruleValue,
            'size' => strlen((string) $value) == $ruleValue,
            'confirmed' => isset($data[$field . '_confirmation']) && $value === $data[$field . '_confirmation'],
            'same' => $value === ($data[$ruleValue] ?? null),
            'different' => $value !== ($data[$ruleValue] ?? null),
            'in' => in_array($value, explode(',', $ruleValue)),
            'not_in' => !in_array($value, explode(',', $ruleValue)),
            default => true,
        };
    }

    private function getErrorMessage(string $rule): string
    {
        return match ($rule) {
            'required' => 'is required',
            'email' => 'must be a valid email',
            'numeric' => 'must be numeric',
            'min' => 'is too small',
            'max' => 'is too large',
            default => 'is invalid',
        };
    }
}
