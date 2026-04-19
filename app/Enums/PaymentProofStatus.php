<?php

namespace App\Enums;

enum PaymentProofStatus: string
{
    case SUBMITTED = 'submitted';
    case VERIFIED = 'verified';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted',
            self::VERIFIED  => 'Verified',
            self::APPROVED  => 'Approved',
            self::REJECTED  => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUBMITTED => 'primary',
            self::VERIFIED  => 'info',
            self::APPROVED  => 'success',
            self::REJECTED  => 'danger',
        };
    }
}
