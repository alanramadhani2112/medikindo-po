<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SupplierInvoice;
use App\Models\Organization;
use App\Exceptions\ImmutabilityViolationException;

echo "Testing Immutability Observer...\n\n";

// Create organization
$org = Organization::factory()->create();
echo "✓ Organization created: {$org->id}\n";

// Create invoice with issued status
$invoice = SupplierInvoice::factory()->create([
    'organization_id' => $org->id,
    'status' => 'issued',
    'total_amount' => '1000.00',
]);
echo "✓ Invoice created: {$invoice->id} with status: {$invoice->status}\n";

// Try to modify total_amount
echo "\nAttempting to modify total_amount from 1000.00 to 2000.00...\n";
$invoice->total_amount = '2000.00';

try {
    $invoice->save();
    echo "✗ FAILED: No exception thrown - Observer is NOT working!\n";
    echo "  Invoice total_amount is now: {$invoice->fresh()->total_amount}\n";
} catch (ImmutabilityViolationException $e) {
    echo "✓ SUCCESS: ImmutabilityViolationException thrown as expected!\n";
    echo "  Message: {$e->getMessage()}\n";
} catch (\Exception $e) {
    echo "✗ FAILED: Wrong exception type: " . get_class($e) . "\n";
    echo "  Message: {$e->getMessage()}\n";
}

echo "\n--- Test Complete ---\n";
