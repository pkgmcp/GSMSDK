<?php

declare(strict_types=1);

namespace GSMSDK\Contracts;

/**
 * HTTP Response Interface
 */
interface ResponseInterface
{
    public function status(int $code): self;
    public function header(string $name, string $value): self;
    public function headers(array $headers): self;
    public function body(string $content): self;
    public function json(mixed $data, int $options = 0): self;
    public function redirect(string $url, int $status = 302): self;
    public function cookie(string $name, string $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, string $sameSite = 'Lax'): self;
    public function forgetCookie(string $name, string $path = '/', string $domain = ''): self;
    public function send(): void;
    public function getStatusCode(): int;
    public function getHeaders(): array;
    public function getBody(): string;
}
