<?php

namespace App\Enums;

enum PaymentProofStatus: string
{
    case SUBMITTED    = 'submitted';
    case VERIFIED     = 'verified';
    case APPROVED     = 'approved';
    case REJECTED     = 'rejected';
    case RECALLED     = 'recalled';
    case RESUBMITTED  = 'resubmitted';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED   => 'Menunggu Review',
            self::VERIFIED    => 'Terverifikasi',
            self::APPROVED    => 'Disetujui',
            self::REJECTED    => 'Ditolak',
            self::RECALLED    => 'Ditarik Kembali',
            self::RESUBMITTED => 'Diajukan Ulang',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUBMITTED   => 'primary',
            self::VERIFIED    => 'info',
            self::APPROVED    => 'success',
            self::REJECTED    => 'danger',
            self::RECALLED    => 'warning',
            self::RESUBMITTED => 'warning',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::RECALLED]);
        // REJECTED is no longer final — can be resubmitted
    }

    public function canBeResubmitted(): bool
    {
        return $this === self::REJECTED;
    }
}
