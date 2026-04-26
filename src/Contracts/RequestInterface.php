<?php

declare(strict_types=1);

namespace GSMSDK\Contracts;

/**
 * HTTP Request Interface
 */
interface RequestInterface
{
    public function method(): string;
    public function uri(): string;
    public function path(): string;
    public function isMethod(string $method): bool;
    public function expectsJson(): bool;
    public function all(): array;
    public function input(string $key, mixed $default = null): mixed;
    public function query(string $key, mixed $default = null): mixed;
    public function post(string $key, mixed $default = null): mixed;
    public function json(string $key, mixed $default = null): mixed;
    public function file(string $key): ?array;
    public function header(string $key, string $default = ''): string;
    public function headers(): array;
    public function server(string $key, mixed $default = null): mixed;
    public function ip(): string;
    public function userAgent(): string;
    public function content(): string;
    public function has(string $key): bool;
}
