<?php

declare(strict_types=1);

namespace GSMSDK\Exceptions;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /** @var array<string, string> */
    private array $errors;

    public function __construct(array $errors, string $message = "Validation failed")
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
