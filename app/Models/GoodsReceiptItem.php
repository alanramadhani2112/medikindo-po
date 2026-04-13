<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'quantity_received' => 'integer',
        'expiry_date'       => 'date',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplierInvoiceLineItems(): HasMany
    {
        return $this->hasMany(SupplierInvoiceLineItem::class);
    }

    public function customerInvoiceLineItems(): HasMany
    {
        return $this->hasMany(CustomerInvoiceLineItem::class);
    }

    /**
     * Get remaining quantity available for invoicing
     * 
     * @return int
     */
    public function getRemainingQuantityAttribute(): int
    {
        $invoiced = $this->supplierInvoiceLineItems()
            ->whereHas('supplierInvoice', function($q) {
                $q->whereNotIn('status', ['cancelled', 'rejected']);
            })
            ->sum('quantity');

        return max(0, $this->quantity_received - (int)$invoiced);
    }

    /**
     * Get total invoiced quantity
     * 
     * @return int
     */
    public function getInvoicedQuantityAttribute(): int
    {
        return (int) $this->supplierInvoiceLineItems()
            ->whereHas('supplierInvoice', function($q) {
                $q->whereNotIn('status', ['cancelled', 'rejected']);
            })
            ->sum('quantity');
    }

    /**
     * Check if this item is fully invoiced
     * 
     * @return bool
     */
    public function isFullyInvoiced(): bool
    {
        return $this->remaining_quantity <= 0;
    }
}
