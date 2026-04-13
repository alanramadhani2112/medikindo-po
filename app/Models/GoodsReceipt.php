<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;
use App\Traits\Filterable;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Filterable;

    // -----------------------------------------------------------------------
    // Status Constants
    // -----------------------------------------------------------------------

    public const STATUS_PARTIAL   = 'partial';
    public const STATUS_COMPLETED = 'completed';

    protected $guarded = ['id'];

    protected $casts = [
        'received_date' => 'date',
        'confirmed_at'  => 'datetime',
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

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function supplierInvoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function customerInvoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    /**
     * Check if this GR has any remaining quantity for invoicing
     * 
     * @return bool
     */
    public function hasRemainingQuantity(): bool
    {
        return $this->items->some(fn($item) => $item->remaining_quantity > 0);
    }

    /**
     * Check if this GR is fully invoiced
     * 
     * @return bool
     */
    public function isFullyInvoiced(): bool
    {
        return $this->items->every(fn($item) => $item->isFullyInvoiced());
    }

    // -----------------------------------------------------------------------
    // State Helpers
    // -----------------------------------------------------------------------

    public function isPartial(): bool   { return $this->status === self::STATUS_PARTIAL; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }
}
