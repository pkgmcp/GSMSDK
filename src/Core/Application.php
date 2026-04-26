<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Contracts\ContainerInterface;
use GSMSDK\Exceptions\ConfigurationException;
use GSMSDK\Traits\Macroable;
use Stringable;

/**
 * GSMSDK Application - Main container and bootstrap for the framework
 *
 * Provides dependency injection, service binding, and lifecycle management
 * for full-stack PHP applications with desktop & mobile capabilities.
 *
 * @implements ContainerInterface<mixed>
 */
readonly class Application implements ContainerInterface, Stringable
{
    use Macroable;

    /** @var string Framework version */
    public const VERSION = '1.0.0';

    /** @var array<string, mixed> Runtime configuration */
    private array $config;

    /** @var array<string, object> Service bindings */
    private array $bindings = [];

    /** @var array<string, bool> Booted services */
    private array $booted = [];

    /**
     * Initialize application with configuration
     *
     * @param  array<string, mixed>  $config  Application configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->defaultConfig(), $config);
        $this->bootstrap();
    }

    /**
     * Get default framework configuration
     *
     * @return array<string, mixed>
     */
    private function defaultConfig(): array
    {
        return [
            'debug' => true,
            'timezone' => 'UTC',
            'charset' => 'UTF-8',
            'environment' => 'production',
            'paths' => [
                'base' => dirname(__DIR__, 2),
                'config' => 'config',
                'storage' => 'storage',
                'cache' => 'storage/cache',
                'logs' => 'storage/logs',
            ],
            'services' => [],
            'providers' => [],
        ];
    }

    /**
     * Bootstrap core framework components
     */
    private function bootstrap(): void
    {
        $this->registerErrorHandling();
        $this->registerTimezone();
        $this->registerCoreBindings();
    }

    /**
     * Register error and exception handling
     */
    private function registerErrorHandling(): void
    {
        if ($this->config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', '0');
        }

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
    }

    /**
     * Register timezone settings
     */
    private function registerTimezone(): void
    {
        date_default_timezone_set($this->config['timezone']);
    }

    /**
     * Register core service bindings
     */
    private function registerCoreBindings(): void
    {
        $this->bind('config', fn() => $this->config);
        $this->bind('app', fn() => $this);
    }

    /**
     * Handle uncaught exceptions
     *
     * @param  \Throwable  $e  Exception to handle
     */
    public function handleException(\Throwable $e): void
    {
        $this->logError($e);

        if ($this->config['debug']) {
            $this->renderDebugException($e);
        } else {
            $this->renderProductionException();
        }
    }

    /**
     * Handle PHP errors
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Log exception to storage
     */
    private function logError(\Throwable $e): void
    {
        $logPath = $this->config['paths']['logs'] . '/error.log';
        $message = sprintf(
            "[%s] %s in %s:%d\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        @file_put_contents($logPath, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Render detailed debug exception
     */
    private function renderDebugException(\Throwable $e): void
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
            'code' => $e->getCode(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Render production-safe exception
     */
    private function renderProductionException(): void
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'An internal error occurred.',
            'code' => 500,
        ]);
    }

    /**
     * Bind a service to the container
     *
     * @template T
     * @param  class-string<T>|string  $abstract  Service identifier
     * @param  callable():T|object     $concrete  Factory or instance
     */
    public function bind(string $abstract, callable|object $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Resolve a service from the container
     *
     * @template T
     * @param  class-string<T>|string  $abstract  Service identifier
     * @return T Resolved service instance
     */
    public function make(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract];
            return $concrete instanceof \Closure ? $concrete($this) : $concrete;
        }

        if (class_exists($abstract)) {
            return new $abstract();
        }

        throw new ConfigurationException("Service not found: {$abstract}");
    }

    /**
     * Check if a service is bound
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || class_exists($abstract);
    }

    /**
     * Boot a service provider
     */
    public function boot(string $provider): void
    {
        if (isset($this->booted[$provider])) {
            return;
        }

        if (class_exists($provider) && method_exists($provider, 'boot')) {
            (new $provider($this))->boot();
            $this->booted[$provider] = true;
        }
    }

    /**
     * Get configuration value
     *
     * @param  string  $key  Dot-notation key
     * @param  mixed   $default  Default value if key not found
     * @return mixed
     */
    public function config(string $key, mixed $default = null): mixed
    {
        $value = $this->config;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Get framework version
     */
    public function version(): string
    {
        return self::VERSION;
    }

    /**
     * Get application environment
     */
    public function environment(): string
    {
        return $this->config['environment'];
    }

    /**
     * Check if running in console/CLI mode
     */
    public function runningInConsole(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return sprintf('GSMSDK v%s [%s]', $this->version(), $this->environment());
    }
}
