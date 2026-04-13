<?php

namespace App\Observers;

use App\Models\PurchaseOrderItem;

class PurchaseOrderItemObserver
{
    /**
     * Auto-calculate subtotal before saving.
     * Replaces MySQL storedAs computed column for cross-DB compatibility.
     */
    public function saving(PurchaseOrderItem $item): void
    {
        $item->subtotal = $item->quantity * $item->unit_price;
    }
}
