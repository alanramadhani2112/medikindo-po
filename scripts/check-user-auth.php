<?php
use App\Models\User;
use App\Models\PaymentProof;
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'alanramadhani21@gmail.com')->first();
$p = PaymentProof::find(7);

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User: " . $user->name . "\n";
echo "Roles: " . json_encode($user->getRoleNames()) . "\n";
echo "Has verify_payment_proof permission: " . ($user->hasPermissionTo('verify_payment_proof') ? 'Yes' : 'No') . "\n";
echo "Has approve_payment permission: " . ($user->hasPermissionTo('approve_payment') ? 'Yes' : 'No') . "\n";
echo "Is Super Admin: " . ($user->hasRole('Super Admin') ? 'Yes' : 'No') . "\n";

if ($p) {
    echo "Payment Proof ID: " . $p->id . "\n";
    echo "Status: " . $p->status->value . "\n";
    echo "Gate check 'verify': " . (Gate::forUser($user)->allows('verify', $p) ? 'Allowed' : 'Denied') . "\n";
}
