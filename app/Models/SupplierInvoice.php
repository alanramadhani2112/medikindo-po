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
}
