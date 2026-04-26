<?php

declare(strict_types=1);

namespace GSMSDK\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    private int $statusCode;

    public function __construct(int $statusCode, string $message = "")
    {
        $this->statusCode = $statusCode;
        parent::__construct($message ?: "HTTP {$statusCode} error");
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
