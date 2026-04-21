<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptDeliveryItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'quantity_received' => 'integer',
        'expiry_date'       => 'date',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptDelivery::class, 'goods_receipt_delivery_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
