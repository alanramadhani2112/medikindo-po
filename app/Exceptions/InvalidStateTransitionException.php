<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidStateTransitionException extends RuntimeException
{
    public function __construct(
        string $fromStatus = '',
        string $toStatus = '',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        $message = $fromStatus && $toStatus
            ? "Transisi dari '{$fromStatus}' ke '{$toStatus}' tidak diizinkan"
            : 'Transisi status tidak valid';

        parent::__construct($message, $code, $previous);
    }
}
