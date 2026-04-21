<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;

use App\Traits\Filterable;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Filterable;

    // -----------------------------------------------------------------------
    // Status Constants — Simplified Workflow (Delivery Outside System)
    // -----------------------------------------------------------------------

    public const STATUS_DRAFT               = 'draft';
    public const STATUS_SUBMITTED           = 'submitted';
    public const STATUS_APPROVED            = 'approved';
    public const STATUS_PARTIALLY_RECEIVED  = 'partially_received';
    public const STATUS_REJECTED            = 'rejected';
    public const STATUS_COMPLETED           = 'completed';

    /**
     * Valid transitions: current_status => [allowed_next_statuses]
     * Enforces strict, no-skip state machine.
     *
     * NOTE: Delivery (shipped/delivered) happens OUTSIDE the system.
     * PO transitions: approved → partially_received (first partial GR)
     *                 partially_received → completed (all items received)
     *                 approved → completed (all items received in one GR)
     */
    public const TRANSITIONS = [
        self::STATUS_DRAFT              => [self::STATUS_SUBMITTED],
        self::STATUS_SUBMITTED          => [self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_APPROVED           => [self::STATUS_PARTIALLY_RECEIVED, self::STATUS_COMPLETED],
        self::STATUS_PARTIALLY_RECEIVED => [self::STATUS_COMPLETED],
        self::STATUS_REJECTED           => [self::STATUS_DRAFT],
        self::STATUS_COMPLETED          => [], // Terminal state
    ];

    protected $fillable = [
        'po_number',
        'organization_id',
        'supplier_id',
        'created_by',
        'status',
        'has_narcotics',
        'requires_extra_approval',
        'total_amount',
        'requested_date',
        'expected_delivery_date',
        'notes',
        'submitted_at',
        'approved_at',
        'sent_at',
        'shipped_at',
        'delivered_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'has_narcotics'           => 'boolean',
            'requires_extra_approval' => 'boolean',
            'total_amount'            => 'decimal:2',
            'requested_date'          => 'date',
            'expected_delivery_date'  => 'date',
            'submitted_at'            => 'datetime',
            'approved_at'             => 'datetime',
            'sent_at'                 => 'datetime',
            'shipped_at'              => 'datetime',
            'delivered_at'            => 'datetime',
            'completed_at'            => 'datetime',
        ];
    }

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function supplierInvoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function customerInvoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    // -----------------------------------------------------------------------
    // State Machine Helpers
    // -----------------------------------------------------------------------

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function isDraft(): bool              { return $this->status === self::STATUS_DRAFT; }
    public function isSubmitted(): bool          { return $this->status === self::STATUS_SUBMITTED; }
    public function isApproved(): bool           { return $this->status === self::STATUS_APPROVED; }
    public function isPartiallyReceived(): bool  { return $this->status === self::STATUS_PARTIALLY_RECEIVED; }
    public function isRejected(): bool           { return $this->status === self::STATUS_REJECTED; }
    public function isCompleted(): bool          { return $this->status === self::STATUS_COMPLETED; }

    /** PO is "in delivery" when approved or partially received */
    public function isInDelivery(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PARTIALLY_RECEIVED], true);
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Recalculate total_amount from items and update has_narcotics flag.
     */
    public function recalculateTotals(): void
    {
        $this->total_amount  = $this->items()->sum('subtotal');
        $this->has_narcotics = $this->items()->whereHas('product', function ($query) {
            $query->where('is_narcotic', true);
        })->exists();
        $this->requires_extra_approval = $this->has_narcotics;
    }
}
