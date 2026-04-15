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

class CustomerInvoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Filterable, HasOptimisticLocking;

    // -----------------------------------------------------------------------
    // Status Constants — AR Invoice Lifecycle
    // -----------------------------------------------------------------------

    public const STATUS_DRAFT        = 'draft';
    public const STATUS_ISSUED       = 'issued';
    public const STATUS_PARTIAL_PAID = 'partial_paid';
    public const STATUS_PAID         = 'paid';
    public const STATUS_VOID         = 'void';

    /**
     * Valid state machine transitions.
     * 
     * DRAFT → ISSUED (after margin check)
     * ISSUED → PARTIAL_PAID | PAID | VOID
     * PARTIAL_PAID → PAID | VOID
     * PAID → terminal
     * VOID → terminal
     */
    public const TRANSITIONS = [
        self::STATUS_DRAFT        => [self::STATUS_ISSUED],
        self::STATUS_ISSUED       => [self::STATUS_PARTIAL_PAID, self::STATUS_PAID, self::STATUS_VOID],
        self::STATUS_PARTIAL_PAID => [self::STATUS_PAID, self::STATUS_VOID],
        self::STATUS_PAID         => [], // terminal
        self::STATUS_VOID         => [], // terminal
    ];

    protected $guarded = ['id'];

    protected $casts = [
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

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
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

    // -----------------------------------------------------------------------
    // State Machine Helpers
    // -----------------------------------------------------------------------

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Transition the invoice to a new status, enforcing the state machine.
     *
     * @param string $newStatus Target status
     * @throws \App\Exceptions\InvalidStateTransitionException If the transition is not allowed
     */
    public function transitionTo(string $newStatus): void
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \App\Exceptions\InvalidStateTransitionException($this->status, $newStatus);
        }

        $this->status = $newStatus;
        $this->save();
    }

    public function isDraft(): bool        { return $this->status === self::STATUS_DRAFT; }
    public function isIssued(): bool       { return $this->status === self::STATUS_ISSUED; }
    public function isPartialPaid(): bool  { return $this->status === self::STATUS_PARTIAL_PAID; }
    public function isPaid(): bool         { return $this->status === self::STATUS_PAID; }
    public function isVoid(): bool         { return $this->status === self::STATUS_VOID; }

    /**
     * Check if the invoice is in an immutable state (cannot be directly modified).
     */
    public function isImmutable(): bool
    {
        return in_array($this->status, [
            self::STATUS_ISSUED,
            self::STATUS_PARTIAL_PAID,
            self::STATUS_PAID,
            self::STATUS_VOID,
        ], true);
    }

    public function canConfirmPayment(): bool
    {
        return in_array($this->status, [self::STATUS_ISSUED, self::STATUS_PARTIAL_PAID], true);
    }
}
