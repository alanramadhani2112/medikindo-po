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

class SupplierInvoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Filterable, HasOptimisticLocking;

    // -----------------------------------------------------------------------
    // Status Constants — mirroring CustomerInvoice spec
    // -----------------------------------------------------------------------

    public const STATUS_ISSUED            = 'issued';
    public const STATUS_PAYMENT_SUBMITTED = 'payment_submitted';
    public const STATUS_PAID              = 'paid';
    public const STATUS_OVERDUE           = 'overdue';

    public const TRANSITIONS = [
        self::STATUS_ISSUED            => [self::STATUS_PAYMENT_SUBMITTED, self::STATUS_OVERDUE],
        self::STATUS_PAYMENT_SUBMITTED => [self::STATUS_PAID],
        self::STATUS_OVERDUE           => [self::STATUS_PAYMENT_SUBMITTED],
        self::STATUS_PAID              => [],
    ];

    protected $guarded = ['id'];

    protected $casts = [
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

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function isIssued(): bool          { return $this->status === self::STATUS_ISSUED; }
    public function isPaymentSubmitted(): bool { return $this->status === self::STATUS_PAYMENT_SUBMITTED; }
    public function isPaid(): bool            { return $this->status === self::STATUS_PAID; }
    public function isOverdue(): bool         { return $this->status === self::STATUS_OVERDUE; }
}
