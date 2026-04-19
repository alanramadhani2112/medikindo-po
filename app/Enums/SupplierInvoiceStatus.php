<?php

namespace App\Enums;

enum SupplierInvoiceStatus: string
{
    case DRAFT = 'draft';
    case VERIFIED = 'verified';
    case PAID = 'paid';
    case OVERDUE = 'overdue';

    /**
     * Check if this status can transition to another status.
     */
    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::DRAFT => in_array($status, [self::VERIFIED, self::OVERDUE]),
            self::VERIFIED => $status === self::PAID,
            self::OVERDUE => $status === self::VERIFIED || $status === self::PAID,
            self::PAID => false, // terminal state
        };
    }

    /**
     * Get all valid transitions from this status.
     */
    public function getValidTransitions(): array
    {
        return match($this) {
            self::DRAFT => [self::VERIFIED, self::OVERDUE],
            self::VERIFIED => [self::PAID],
            self::OVERDUE => [self::VERIFIED, self::PAID],
            self::PAID => [],
        };
    }

    /**
     * Get human-readable label for the status.
     */
    public function getLabel(): string
    {
        return match($this) {
            self::DRAFT             => 'Draft / Baru',
            self::VERIFIED          => 'Diverifikasi',
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
            self::DRAFT => 'badge-light-primary',
            self::VERIFIED => 'badge-light-info',
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
