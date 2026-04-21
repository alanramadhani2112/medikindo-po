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
use App\Enums\SupplierInvoiceStatus;

class SupplierInvoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Filterable, HasOptimisticLocking;

    protected $guarded = ['id'];

    protected $casts = [
        'status'                      => SupplierInvoiceStatus::class,
        'due_date'                    => 'date',
        'distributor_invoice_date'    => 'date',
        'total_amount'                => 'decimal:2',
        'paid_amount'                 => 'decimal:2',
        'subtotal_amount'             => 'decimal:2',
        'discount_amount'             => 'decimal:2',
        'tax_amount'                  => 'decimal:2',
        'issued_at'                   => 'datetime',
        'payment_submitted_at'        => 'datetime',
        'verified_at'                 => 'datetime',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------
    
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
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
        return $this->hasMany(SupplierInvoiceLineItem::class);
    }

    // -----------------------------------------------------------------------
    // State Machine Helpers
    // -----------------------------------------------------------------------

    public function canTransitionTo(SupplierInvoiceStatus $status): bool
    {
        return $this->status->canTransitionTo($status);
    }

    public function isDraft(): bool     { return $this->status === SupplierInvoiceStatus::DRAFT; }
    public function isVerified(): bool  { return $this->status === SupplierInvoiceStatus::VERIFIED; }
    public function isPaid(): bool      { return $this->status === SupplierInvoiceStatus::PAID; }
    public function isOverdue(): bool   { return $this->status === SupplierInvoiceStatus::OVERDUE; }

    // -----------------------------------------------------------------------
    // Finance Engine Helpers
    // -----------------------------------------------------------------------

    public function getOutstandingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function isOverdueByDate(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! $this->isPaid();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->due_date || $this->isPaid()) return 0;
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

    /**
     * Apply credit note to reduce AP balance.
     */
    public function applyCreditNote(\App\Models\CreditNote $creditNote): void
    {
        if ($creditNote->supplier_invoice_id !== $this->id) {
            throw new \DomainException('Credit note does not belong to this supplier invoice.');
        }

        $creditAmount    = (float) $creditNote->total_amount;
        $remainingBalance = $this->outstanding_amount;

        if ($creditAmount >= $remainingBalance) {
            $this->update([
                'paid_amount' => $this->total_amount,
                'status'      => SupplierInvoiceStatus::PAID,
            ]);
        } else {
            $this->increment('paid_amount', $creditAmount);
        }
    }
}
