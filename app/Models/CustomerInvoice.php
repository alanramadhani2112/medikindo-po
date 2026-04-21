<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;
use App\Traits\Filterable;
use App\Traits\HasOptimisticLocking;
use App\Enums\CustomerInvoiceStatus;
use App\Exceptions\InvalidStateTransitionException;

class CustomerInvoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Filterable, HasOptimisticLocking;

    protected $guarded = ['id'];

    protected $casts = [
        'status'               => CustomerInvoiceStatus::class,
        'due_date'             => 'date',
        'total_amount'         => 'decimal:2',
        'paid_amount'          => 'decimal:2',
        'subtotal_amount'      => 'decimal:2',
        'discount_amount'      => 'decimal:2',
        'tax_amount'           => 'decimal:2',
        'surcharge'            => 'decimal:2',
        'ematerai_fee'         => 'decimal:2',
        'print_count'          => 'integer',
        'issued_at'            => 'datetime',
        'payment_submitted_at' => 'datetime',
        'verified_at'          => 'datetime',
        'last_printed_at'      => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    /**
     * Anti-Phantom Link: the SupplierInvoice that this AR invoice is based on.
     * A CustomerInvoice MUST reference a verified SupplierInvoice.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(CustomerInvoiceLineItem::class);
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    // -----------------------------------------------------------------------
    // State Machine Helpers
    // -----------------------------------------------------------------------

    /**
     * Check if this invoice can transition to the given status.
     */
    public function canTransitionTo(CustomerInvoiceStatus $status): bool
    {
        return $this->status->canTransitionTo($status);
    }

    /**
     * Transition the invoice to a new status, enforcing the state machine.
     *
     * @param CustomerInvoiceStatus $newStatus Target status
     * @throws InvalidStateTransitionException If the transition is not allowed
     */
    public function transitionTo(CustomerInvoiceStatus $newStatus): void
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new InvalidStateTransitionException(
                "Tidak dapat mengubah status dari '{$this->status->getLabel()}' ke '{$newStatus->getLabel()}'"
            );
        }

        $this->status = $newStatus;
        $this->save();
    }

    /**
     * Get the status badge HTML for display.
     */
    public function getStatusBadge(): string
    {
        return '<span class="badge ' . $this->status->getBadgeClass() . '">' . $this->status->getLabel() . '</span>';
    }

    // Status check helpers using enum
    public function isDraft(): bool        { return $this->status === CustomerInvoiceStatus::DRAFT; }
    public function isIssued(): bool       { return $this->status === CustomerInvoiceStatus::ISSUED; }
    public function isPartialPaid(): bool  { return $this->status === CustomerInvoiceStatus::PARTIAL_PAID; }
    public function isPaid(): bool         { return $this->status === CustomerInvoiceStatus::PAID; }
    public function isVoid(): bool         { return $this->status === CustomerInvoiceStatus::VOID; }

    public function isImmutable(): bool
    {
        return $this->status->isImmutable();
    }

    public function canConfirmPayment(): bool
    {
        return $this->status->canAcceptPayment();
    }

    public function isOverdueByDate(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status->canAcceptPayment();
    }

    public function getOutstandingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->due_date || $this->isPaid() || $this->isVoid()) return 0;
        return max(0, (int) now()->startOfDay()->diffInDays($this->due_date, false) * -1);
    }

    public function getAgingBucketAttribute(): string
    {
        $days = $this->days_overdue;
        if ($days <= 0)  return 'current';
        if ($days <= 30) return '1-30';
        if ($days <= 60) return '31-60';
        if ($days <= 90) return '61-90';
        return '90+';
    }

    // -----------------------------------------------------------------------
    // Credit Note Handling
    // -----------------------------------------------------------------------

    /**
     * Apply credit note to reduce invoice balance
     */
    public function applyCreditNote(CreditNote $creditNote): void
    {
        if ($creditNote->customer_invoice_id !== $this->id) {
            throw new \DomainException('Credit note does not belong to this invoice');
        }

        if (!$creditNote->isIssued()) {
            throw new \DomainException('Credit note must be issued before applying');
        }

        // Calculate new amounts after credit note
        $creditAmount = $creditNote->total_amount;
        
        // If credit note amount >= remaining balance, mark as paid
        $remainingBalance = $this->total_amount - $this->paid_amount;
        
        if ($creditAmount >= $remainingBalance) {
            $this->paid_amount = $this->total_amount;
            $this->transitionTo(CustomerInvoiceStatus::PAID);
        } else {
            $this->paid_amount += $creditAmount;
            if ($this->paid_amount > 0 && $this->paid_amount < $this->total_amount) {
                $this->transitionTo(CustomerInvoiceStatus::PARTIAL_PAID);
            }
        }

        $this->save();
    }

    /**
     * Get total credit note amount applied to this invoice
     */
    public function getTotalCreditNoteAmount(): float
    {
        return $this->creditNotes()
            ->where('status', CreditNote::STATUS_APPLIED)
            ->sum('total_amount');
    }

    /**
     * Get remaining balance after payments and credit notes
     */
    public function getRemainingBalance(): float
    {
        return $this->total_amount - $this->paid_amount - $this->getTotalCreditNoteAmount();
    }
}
