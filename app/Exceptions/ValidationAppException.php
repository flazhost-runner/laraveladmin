<?php

namespace App\Exceptions;

class ValidationAppException extends AppException
{
    /** @var array<string, string[]> */
    protected array $errors;

    /**
     * @param  array<string, string[]>  $errors  Field-level validation errors.
     */
    public function __construct(string $message = 'Validation failed', array $errors = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
