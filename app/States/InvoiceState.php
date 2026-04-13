<?php

namespace App\States;

use DomainException;

class InvoiceState
{
    public const UNPAID = 'unpaid';
    public const PARTIAL = 'partial';
    public const PAID = 'paid';

    public static function ensureCanApplyPayment(string $currentStatus): void
    {
        if ($currentStatus === self::PAID) {
            throw new DomainException("Invoice is already fully paid.");
        }
    }
}
