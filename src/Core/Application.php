<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Contracts\ContainerInterface;
use GSMSDK\Core\AI\AiRouter;
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
class Application implements ContainerInterface, Stringable
{
    use Macroable;

    /** @var array<string, callable> Registered services */
    private array $services = [];

    /** @var array<string, mixed> Cached instantiated services */
    private array $resolved = [];

    /** @var array<string, mixed> Application configuration */
    private array $config;

    /** @var ?AiRouter AI Router instance */
    private ?AiRouter $aiRouter = null;

    /**
     * @param  array<string, mixed>  $config  Application configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'debug' => false,
            'environment' => 'production',
            'paths' => [
                'base' => dirname(__DIR__, 2),
                'views' => dirname(__DIR__, 2) . '/resources/views',
                'controllers' => dirname(__DIR__, 2) . '/app/Controllers',
            ],
            'app' => [
                'name' => 'GSMSDK',
                'url' => 'http://localhost:8000',
                'version' => '2.0.0',
            ],
            'database' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
            'api' => [
                'rate_limit' => 100,
                'rate_window' => 60,
                'throttle' => true,
                'cache' => true,
                'cache_ttl' => 300,
            ],
        ], $config);

        $this->setupDirectories();
        $this->setupDatabase();
        $this->registerCoreServices();
    }

    private function setupDirectories(): void
    {
        $storagePath = $this->storagePath();
        $directories = [$storagePath, $storagePath . '/cache', $storagePath . '/logs', $storagePath . '/sessions'];
        foreach ($directories as $dir) {
            if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        }
    }

    private function setupDatabase(): void
    {
        if ($this->config['database']['driver'] !== 'sqlite') { return; }
        $dbFile = $this->storagePath() . '/database.sqlite';
        $this->config['database']['database'] = $dbFile;
        if (!file_exists($dbFile)) { touch($dbFile); }
    }

    private function registerCoreServices(): void
    {
        $this->services[Application::class] = $this;
        $this->services[ContainerInterface::class] = $this;
        $this->services['auth'] = fn() => new \GSMSDK\Core\Auth\AuthManager($this);
        $this->services['view'] = fn() => new \GSMSDK\Core\View($this->config['paths']['views'], $this->config['paths']['views'] . '/layouts');
        $this->services['aiRouter'] = fn() => new AiRouter($this);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) { return $default; }
            $value = $value[$k];
        }
        return $value;
    }

    public function storagePath(): string { return $this->config['paths']['base'] . '/storage'; }
    public function version(): string { return $this->config['app']['version'] ?? '2.0.0'; }
    public function environment(): string { return $this->config['environment'] ?? 'production'; }

    public function aiRouter(): AiRouter
    {
        if ($this->aiRouter === null) { $this->aiRouter = $this->get('aiRouter'); }
        return $this->aiRouter;
    }

    public function run(): void { $mvc = new MvcApplication($this); $mvc->run(); }

    public function runApi(): void { $api = new \GSMSDK\Core\Api\ApiApplication($this); $api->run(); }
    public function basePath(): string { return $this->config['paths']['base']; }
    public function viewPath(): string { return $this->config['paths']['views']; }

    public function get(string $id): mixed
    {
        if (isset($this->resolved[$id])) { return $this->resolved[$id]; }
        if (isset($this->services[$id])) {
            $factory = $this->services[$id];
            return $this->resolved[$id] = $factory($this);
        }
        throw new ConfigurationException("Service not found: {$id}");
    }

    public function has(string $id): bool { return isset($this->services[$id]); }
    public function set(string $id, callable $factory): void { $this->services[$id] = $factory; unset($this->resolved[$id]); }
    public function bind(string $abstract, callable|object $concrete): void { $this->services[$interface] = fn() => new $concrete($this); }
    public function singleton(string $id, callable $factory): void
    {
        $this->services[$id] = function () use ($factory, $id) {
            if (!isset($this->resolved[$id])) { $this->resolved[$id] = $factory($this); }
            return $this->resolved[$id];
        };
    }

    public function make(string $class, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass($class);
        if (!$reflection->isInstantiable()) { throw new ConfigurationException("Class {$class} is not instantiable"); }
        $constructor = $reflection->getConstructor();
        if (!$constructor) { return new $class(); }
        $args = [];
        foreach ($constructor->getParameters() as $param) {
            if (array_key_exists($param->getName(), $parameters)) { $args[] = $parameters[$param->getName()]; }
            elseif ($param->getType() && !$param->getType()->isBuiltin()) {
                $typeName = $param->getType()->getName();
                $args[] = $this->has($typeName) ? $this->get($typeName) : null;
            } elseif ($param->isDefaultValueAvailable()) { $args[] = $param->getDefaultValue(); }
            else { $args[] = null; }
        }
        return $reflection->newInstanceArgs($args);
    }

    public function __toString(): string
    {
        return sprintf('GSMSDK Application v%s (%s environment)', $this->version(), $this->environment());
    }
}
