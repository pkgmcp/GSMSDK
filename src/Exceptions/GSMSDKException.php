<?php

declare(strict_types=1);

namespace GSMSDK\Exceptions;

use RuntimeException;

class GSMSDKException extends RuntimeException
{
    public static function serviceNotFound(string $service): self
    {
        return new self("Service not found: {$service}");
    }

    public static function configurationError(string $message): self
    {
        return new self("Configuration error: {$message}");
    }

    public static function runtimeError(string $operation, ?\Throwable $previous = null): self
    {
        return new self("Runtime error during {$operation}", 0, $previous);
    }
}
