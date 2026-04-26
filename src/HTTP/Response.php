<?php

declare(strict_types=1);

namespace GSMSDK\HTTP;

use GSMSDK\Contracts\ResponseInterface;

/**
 * HTTP Response builder
 *
 * Immutable fluent interface for building HTTP responses with
 * status codes, headers, and body content.
 */
class Response implements ResponseInterface
{
    /** @var int HTTP status code */
    private int $statusCode = 200;

    /** @var array<string, string> Response headers */
    private array $headers = [];

    /** @var string Response body */
    private string $body = '';

    /** @var array<int, array{version: string, status: int, reason: string}>> Response history for redirects */
    private array $history = [];

    /**
     * Set HTTP status code
     */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set response header
     *
     * @param  string  $name  Header name
     * @param  string  $value  Header value
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set multiple headers
     *
     * @param  array<string, string>  $headers  Headers to set
     */
    public function headers(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }
        return $this;
    }

    /**
     * Set response body
     *
     * @param  string  $content  Body content
     */
    public function body(string $content): self
    {
        $this->body = $content;
        return $this;
    }

    /**
     * Set JSON body
     *
     * @param  mixed  $data  Data to encode
     * @param  int  $options  JSON encoding options
     */
    public function json(mixed $data, int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES): self
    {
        $this->header('Content-Type', 'application/json');
        $this->body = json_encode($data, $options);
        return $this;
    }

    /**
     * Set redirect response
     *
     * @param  string  $url  Redirect URL
     * @param  int  $status  HTTP status code
     */
    public function redirect(string $url, int $status = 302): self
    {
        $this->statusCode = $status;
        $this->header('Location', $url);
        return $this;
    }

    /**
     * Set cookie
     *
     * @param  string  $name  Cookie name
     * @param  string  $value  Cookie value
     * @param  int  $expires  Expiration timestamp
     * @param  string  $path  Cookie path
     * @param  string  $domain  Cookie domain
     * @param  bool  $secure  Secure flag
     * @param  bool  $httpOnly  HttpOnly flag
     * @param  string  $sameSite  SameSite attribute
     */
    public function cookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $cookie = sprintf(
            '%s=%s',
            urlencode($name),
            urlencode($value)
        );

        if ($expires > 0) {
            $cookie .= sprintf('; Expires=%s', gmdate('D, d M Y H:i:s T', $expires));
        }

        $cookie .= sprintf('; Path=%s', $path);

        if ($domain !== '') {
            $cookie .= sprintf('; Domain=%s', $domain);
        }

        if ($secure) {
            $cookie .= '; Secure';
        }

        if ($httpOnly) {
            $cookie .= '; HttpOnly';
        }

        if ($sameSite !== '') {
            $cookie .= sprintf('; SameSite=%s', $sameSite);
        }

        $this->header('Set-Cookie', $cookie);
        return $this;
    }

    /**
     * Remove cookie
     *
     * @param  string  $name  Cookie name
     * @param  string  $path  Cookie path
     * @param  string  $domain  Cookie domain
     */
    public function forgetCookie(string $name, string $path = '/', string $domain = ''): self
    {
        return $this->cookie($name, '', 1, $path, $domain);
    }

    /**
     * Send response to client
     */
    public function send(): void
    {
        if (headers_sent()) {
            return;
        }

        $this->sendHeaders();
        $this->sendBody();
    }

    /**
     * Send HTTP headers
     */
    private function sendHeaders(): void
    {
        $statusText = $this->getStatusText($this->statusCode);
        $protocol = $this->serverProtocol();

        header(sprintf('%s %d %s', $protocol, $this->statusCode, $statusText), true, $this->statusCode);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value), false, $this->statusCode);
        }
    }

    /**
     * Send response body
     */
    private function sendBody(): void
    {
        echo $this->body;
    }

    /**
     * Get HTTP status text for code
     *
     * @param  int  $code  HTTP status code
     */
    private function getStatusText(int $code): string
    {
        $statuses = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
        ];

        return $statuses[$code] ?? 'Unknown Status';
    }

    /**
     * Get server protocol
     */
    private function serverProtocol(): string
    {
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        return in_array($protocol, ['HTTP/1.1', 'HTTP/1.0', 'HTTP/2.0'], true) ? $protocol : 'HTTP/1.1';
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get body
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Render a view
     */
    public function view(string $name, array $data = []): self
    {
        $viewPath = $this->findView($name);
        if (!$viewPath) {
            $this->status(404);
            $this->body = "View not found: {$name}";
            return $this;
        }
        
        extract($data);
        ob_start();
        include $viewPath;
        $this->body = ob_get_clean();
        $this->header('Content-Type', 'text/html; charset=UTF-8');
        return $this;
    }

    /**
     * Send JSON response
     */

    /**
     * Find view file
     */
    private function findView(string $name): ?string
    {
        $paths = [
            dirname(__DIR__, 2) . "/resources/views/{$name}.gsm.php",
            dirname(__DIR__, 2) . "/resources/views/{$name}.php",
            dirname(__DIR__, 2) . "/public/views/{$name}.php",
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }
}
