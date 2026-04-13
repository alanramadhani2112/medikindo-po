<?php

namespace App\States;

use App\Models\PurchaseOrder;
use DomainException;

class POState
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const UNDER_REVIEW = 'under_review';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const SENT_TO_SUPPLIER = 'sent_to_supplier';

    public static function canTransitionTo(PurchaseOrder $po, string $newState): bool
    {
        $transitions = [
            self::DRAFT => [self::SUBMITTED],
            self::SUBMITTED => [self::UNDER_REVIEW, self::REJECTED],
            self::UNDER_REVIEW => [self::APPROVED, self::REJECTED],
            self::APPROVED => [self::SENT_TO_SUPPLIER],
            self::REJECTED => [self::DRAFT],
            self::SENT_TO_SUPPLIER => [], 
        ];

        return in_array($newState, $transitions[$po->status] ?? []);
    }

    public static function ensureCanTransition(PurchaseOrder $po, string $newState): void
    {
        if (!self::canTransitionTo($po, $newState)) {
            throw new DomainException("Cannot transition PO from [{$po->status}] to [{$newState}].");
        }
    }
}
