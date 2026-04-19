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
     * Get CSS class for badge styling.
     */
    public function getBadgeClass(): string
    {
        return match($this) {
            self::DRAFT => 'badge-light-secondary',
            self::ISSUED => 'badge-light-warning',
            self::PARTIAL_PAID => 'badge-light-info',
            self::PAID => 'badge-light-success',
            self::VOID => 'badge-light-danger',
        };
    }

    /**
     * Check if the status is immutable (cannot be directly modified).
     */
    public function isImmutable(): bool
    {
        return in_array($this, [self::PAID, self::VOID]);
    }

    /**
     * Check if this status can transition to another status.
     */
    public function canTransitionTo(self $target): bool
    {
        return match($this) {
            self::DRAFT => in_array($target, [self::ISSUED, self::VOID]),
            self::ISSUED => in_array($target, [self::PARTIAL_PAID, self::PAID, self::VOID]),
            self::PARTIAL_PAID => in_array($target, [self::PAID, self::VOID]),
            self::PAID, self::VOID => false,
        };
    }

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
