<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvoiceLineItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity'            => 'decimal:3',
        'unit_price'          => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount'     => 'decimal:2',
        'tax_rate'            => 'decimal:2',
        'tax_amount'          => 'decimal:2',
        'line_total'          => 'decimal:2',
        'expiry_date'         => 'date',
    ];

    /**
     * Get the supplier invoice that owns this line item
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    /**
     * Get the product associated with this line item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the goods receipt item that this invoice line item is based on
     */
    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    /**
     * Calculate line subtotal (quantity * unit_price)
     * 
     * @return string
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
     * Calculate taxable amount (subtotal - discount)
     * 
     * @return string
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
