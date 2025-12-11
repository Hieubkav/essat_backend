<?php

namespace App\Exceptions;

use Exception;

/**
 * API Exception
 * 
 * Custom exception for API responses
 */
class ApiException extends Exception
{
    public function __construct(
        string $message = 'API Error',
        int $code = 400,
        ?Exception $previous = null,
        protected mixed $data = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
