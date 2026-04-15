<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateMirrorException extends RuntimeException
{
    public function __construct(
        string $message = 'Draft CustomerInvoice sudah ada untuk SupplierInvoice ini',
        int $code = 409,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
