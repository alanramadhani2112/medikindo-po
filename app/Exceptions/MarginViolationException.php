<?php

namespace App\Exceptions;

use RuntimeException;

class MarginViolationException extends RuntimeException
{
    private array $violations;

    public function __construct(
        string $message = 'Selling price lebih rendah dari cost price pada satu atau lebih baris',
        array $violations = [],
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->violations = $violations;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}
