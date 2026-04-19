<?php

use App\Models\GoodsReceipt;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'alanramadhani21@gmail.com')->first(); 
if (!$user) { echo "User not found\n"; exit; }

$query = GoodsReceipt::with(['purchaseOrder.supplier', 'items.purchaseOrderItem.product'])
    ->where('status', 'completed')
    ->whereHas('purchaseOrder', function($q) {
        $q->whereNotNull('supplier_id');
    });

if (! $user->hasRole('Super Admin')) {
    $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
}

$goodsReceipts = $query->get()->filter(function($gr) {
    return $gr->hasRemainingQuantity();
});

echo "Total GRs: " . $goodsReceipts->count() . "\n";

$grData = $goodsReceipts->map(fn($gr) => [
    'id' => $gr->id,
    'gr_number' => $gr->gr_number,
    'po_number' => $gr->purchaseOrder?->po_number ?? '—',
    'supplier_name' => $gr->purchaseOrder?->supplier?->name ?? '—',
    'items' => $gr->items->map(fn($item) => [
        'id' => $item->id,
        'product_name' => $item->product?->name ?? '—',
        'product_unit' => $item->product?->unit ?? 'unit',
        'batch_no' => $item->batch_no,
        'expiry_date' => $item->expiry_date?->format('Y-m-d'),
        'quantity_received' => $item->quantity_received,
        'remaining_quantity' => $item->remaining_quantity,
        'unit_price' => $item->purchaseOrderItem?->unit_price ?? 0,
        'discount_percent' => $item->purchaseOrderItem?->discount_percent ?? 0,
    ])->toArray()
])->toArray();

echo json_encode($grData, JSON_PRETTY_PRINT) . "\n";
