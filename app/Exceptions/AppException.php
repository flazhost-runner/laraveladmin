<?php

namespace App\Exceptions;

use RuntimeException;

class AppException extends RuntimeException
{
    public function __construct(string $message = 'An error occurred', int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
