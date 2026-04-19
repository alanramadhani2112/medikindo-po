<?php

namespace App\Enums;

enum SupplierInvoiceStatus: string
{
    case ISSUED = 'issued';
    case VERIFIED = 'verified';
    case PAYMENT_SUBMITTED = 'payment_submitted';
    case PAID = 'paid';
    case OVERDUE = 'overdue';

    /**
     * Check if this status can transition to another status.
     */
    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::ISSUED => in_array($status, [self::VERIFIED, self::PAYMENT_SUBMITTED, self::OVERDUE]),
            self::VERIFIED => in_array($status, [self::PAYMENT_SUBMITTED, self::PAID]),
            self::PAYMENT_SUBMITTED => $status === self::PAID,
            self::OVERDUE => $status === self::PAYMENT_SUBMITTED,
            self::PAID => false, // terminal state
        };
    }

    /**
     * Get all valid transitions from this status.
     */
    public function getValidTransitions(): array
    {
        return match($this) {
            self::ISSUED => [self::VERIFIED, self::PAYMENT_SUBMITTED, self::OVERDUE],
            self::VERIFIED => [self::PAYMENT_SUBMITTED, self::PAID],
            self::PAYMENT_SUBMITTED => [self::PAID],
            self::OVERDUE => [self::PAYMENT_SUBMITTED],
            self::PAID => [],
        };
    }

    /**
     * Get human-readable label for the status.
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ISSUED            => 'Menunggu Pembayaran',
            self::VERIFIED          => 'Diverifikasi',
            self::PAYMENT_SUBMITTED => 'Pembayaran Diproses',
            self::PAID              => 'Lunas',
            self::OVERDUE           => 'Jatuh Tempo',
        };
    }

    /**
     * Get CSS class for badge styling.
     */
    public function getBadgeClass(): string
    {
        return match($this) {
            self::ISSUED => 'badge-light-primary',
            self::VERIFIED => 'badge-light-info',
            self::PAYMENT_SUBMITTED => 'badge-light-warning',
            self::PAID => 'badge-light-success',
            self::OVERDUE => 'badge-light-danger',
        };
    }

    /**
     * Check if status is final.
     */
    public function isFinal(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Get all possible status values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
