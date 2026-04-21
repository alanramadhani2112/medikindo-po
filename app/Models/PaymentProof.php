<?php

namespace App\Models;

use App\Enums\PaymentProofStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PaymentProof extends Model
{
    protected $fillable = [
        'customer_invoice_id',
        'submitted_by',
        'amount',
        'payment_type',
        'payment_date',
        'bank_reference',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'recall_reason',
        'recalled_at',
        'correction_of_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'status'       => PaymentProofStatus::class,
        'verified_at'  => 'datetime',
        'approved_at'  => 'datetime',
        'recalled_at'  => 'datetime',
    ];

    /**
     * Relationships
     */
    public function customerInvoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paymentDocuments(): HasMany
    {
        return $this->hasMany(PaymentDocument::class);
    }

    /**
     * Scopes
     */
    public function scopeByStatus(Builder $query, PaymentProofStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByHealthcareUser(Builder $query, int $userId): Builder
    {
        return $query->where('submitted_by', $userId);
    }

    /**
     * Helpers
     */
    public function canBeVerified(): bool
    {
        return $this->status === PaymentProofStatus::SUBMITTED;
    }

    public function canBeApproved(): bool
    {
        return $this->status === PaymentProofStatus::VERIFIED || $this->status === PaymentProofStatus::SUBMITTED;
    }

    public function isSubmitted(): bool
    {
        return $this->status === PaymentProofStatus::SUBMITTED;
    }

    public function canBeRecalled(): bool
    {
        return $this->status === PaymentProofStatus::SUBMITTED;
    }

    public function isRecalled(): bool
    {
        return $this->status === PaymentProofStatus::RECALLED;
    }

    public function canBeCorrected(): bool
    {
        return $this->status === PaymentProofStatus::APPROVED;
    }

    /**
     * The corrected proof this record replaces (for Super Admin corrections).
     */
    public function correctionOf(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentProof::class, 'correction_of_id');
    }

    public function corrections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentProof::class, 'correction_of_id');
    }
}
