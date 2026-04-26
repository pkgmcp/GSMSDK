<?php

declare(strict_types=1);

namespace GSMSDK\Contracts;

/**
 * Dependency Injection Container Interface
 *
 * Defines the contract for service container implementations
 * following PSR-11 standards with framework-specific extensions.
 */
interface ContainerInterface
{
    /**
     * Resolve a service from the container
     *
     * @template T
     * @param  class-string<T>|string  $id  Service identifier
     * @return T Resolved service instance
     */
    public function make(string $id): mixed;

    /**
     * Check if a service is available in the container
     *
     * @param  string  $id  Service identifier
     * @return bool True if service can be resolved
     */
    public function has(string $id): bool;

    /**
     * Bind a service to the container
     *
     * @param  string  $abstract  Service identifier
     * @param  callable|object  $concrete  Factory or instance
     */
    public function bind(string $abstract, callable|object $concrete): void;
}
