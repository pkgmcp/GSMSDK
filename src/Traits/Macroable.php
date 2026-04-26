<?php

declare(strict_types=1);

namespace GSMSDK\Traits;

trait Macroable
{
    private static array $macros = [];

    public static function macro(string $name, callable $macro): void
    {
        static::$macros[$name] = $macro;
    }

    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    public static function flushMacros(): void
    {
        static::$macros = [];
    }

    public function __call(string $method, array $parameters): mixed
    {
        if (!isset(static::$macros[$method])) {
            throw new \BadMethodCallException(
                sprintf('Method %s::%s does not exist.', static::class, $method)
            );
        }
        return (static::$macros[$method])(...$parameters);
    }

    public static function __callStatic(string $method, array $parameters): mixed
    {
        if (!isset(static::$macros[$method])) {
            throw new \BadMethodCallException(
                sprintf('Static method %s::%s does not exist.', static::class, $method)
            );
        }
        return (static::$macros[$method])(...$parameters);
    }
}
