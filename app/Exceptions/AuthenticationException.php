<?php

namespace App\Exceptions;

use Exception;

/**
 * Custom Authentication Exception
 *
 * Dùng cho các lỗi xác thực như login sai, password sai, etc.
 */
class AuthenticationException extends Exception
{
    public function __construct(
        string $message = 'Authentication failed',
        int $code = 401,
        ?Exception $previous = null,
        protected ?array $errors = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
