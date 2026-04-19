<?php

namespace App\Enums;

enum CustomerInvoiceStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PARTIAL_PAID = 'partial_paid';
    case PAID = 'paid';
    case VOID = 'void';

    /**
     * Get human-readable label for the status.
     */
    public function getLabel(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::ISSUED => 'Menunggu Pembayaran',
            self::PARTIAL_PAID => 'Dibayar Sebagian',
            self::PAID => 'Lunas',
            self::VOID => 'Dibatalkan',
        };
    }

    /**
     * Check if status can accept payments.
     */
    public function canAcceptPayment(): bool
    {
        return in_array($this, [self::ISSUED, self::PARTIAL_PAID]);
    }

    /**
     * Check if status is considered "active" for reporting.
     */
    public function isActive(): bool
    {
        return in_array($this, [self::ISSUED, self::PARTIAL_PAID, self::PAID]);
    }

    /**
     * Get all possible status values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
