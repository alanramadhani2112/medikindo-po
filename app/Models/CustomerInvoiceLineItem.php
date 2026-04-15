<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerInvoiceLineItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity'            => 'decimal:3',
        'unit_price'          => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount'     => 'decimal:2',
        'tax_rate'            => 'decimal:2',
        'tax_amount'          => 'decimal:2',
        'line_total'          => 'decimal:2',
        'expiry_date'         => 'date',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Get the customer invoice that owns this line item.
     */
    public function customerInvoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    /**
     * Get the product associated with this line item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the goods receipt item that this invoice line item is based on.
     */
    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    /**
     * Mirror Link: the SupplierInvoiceLineItem this AR line item mirrors.
     * Critical for BPOM audit trail — links AR to AP at line-item level.
     */
    public function supplierItem(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoiceLineItem::class, 'supplier_invoice_item_id');
    }

    // -----------------------------------------------------------------------
    // Calculated Attributes
    // -----------------------------------------------------------------------

    /**
     * Calculate line subtotal (quantity * unit_price).
     */
    public function getLineSubtotalAttribute(): string
    {
        $calculator = app(\App\Services\BCMathCalculatorService::class);
        return $calculator->multiply(
            (string) $this->quantity,
            (string) $this->unit_price
        );
    }

    /**
     * Calculate taxable amount (subtotal - discount).
     */
    public function getTaxableAmountAttribute(): string
    {
        $calculator = app(\App\Services\BCMathCalculatorService::class);
        return $calculator->subtract(
            $this->line_subtotal,
            (string) $this->discount_amount
        );
    }
}
