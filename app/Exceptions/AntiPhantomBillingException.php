<?php

namespace App\Exceptions;

use RuntimeException;

class AntiPhantomBillingException extends RuntimeException
{
    public function __construct(
        string $message = 'CustomerInvoice tidak dapat dibuat tanpa referensi SupplierInvoice yang valid',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
