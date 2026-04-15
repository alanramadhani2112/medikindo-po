<?php

namespace App\Exceptions;

use RuntimeException;

class PriceListNotFoundException extends RuntimeException
{
    public function __construct(
        string $message = 'Harga jual untuk produk belum dikonfigurasi',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
