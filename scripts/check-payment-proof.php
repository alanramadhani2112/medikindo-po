<?php
use App\Models\PaymentProof;
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = PaymentProof::find(7);
if ($p) {
    echo "ID: " . $p->id . "\n";
    echo "Status: " . $p->status->value . "\n";
    echo "Can be verified: " . ($p->canBeVerified() ? 'Yes' : 'No') . "\n";
    echo "Can be approved: " . ($p->canBeApproved() ? 'Yes' : 'No') . "\n";
} else {
    echo "Payment Proof ID 5 not found.\n";
}
