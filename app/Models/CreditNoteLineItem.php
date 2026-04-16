<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_id',
        'product_id',
        'customer_invoice_line_item_id',
        'supplier_invoice_line_item_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customerInvoiceLineItem(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoiceLineItem::class);
    }

    public function supplierInvoiceLineItem(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoiceLineItem::class);
    }

    // -----------------------------------------------------------------------
    // Business Logic
    // -----------------------------------------------------------------------

    /**
     * Calculate subtotal and tax amount
     */
    public function calculateAmounts(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
    }
}