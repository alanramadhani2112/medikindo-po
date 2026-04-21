<?php

namespace App\Enums;

enum PaymentProofStatus: string
{
    case SUBMITTED = 'submitted';
    case VERIFIED  = 'verified';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case RECALLED  = 'recalled';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Menunggu Review',
            self::VERIFIED  => 'Terverifikasi',
            self::APPROVED  => 'Disetujui',
            self::REJECTED  => 'Ditolak',
            self::RECALLED  => 'Ditarik Kembali',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUBMITTED => 'primary',
            self::VERIFIED  => 'info',
            self::APPROVED  => 'success',
            self::REJECTED  => 'danger',
            self::RECALLED  => 'warning',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::RECALLED]);
    }
}
