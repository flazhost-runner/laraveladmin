<?php

namespace App\Exceptions;

class ConflictException extends AppException
{
    public function __construct(string $message = 'Conflict', ?\Throwable $previous = null)
    {
        parent::__construct($message, 409, $previous);
    }
}
