<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use Illuminate\Http\Request;

echo "=== TESTING APPROVAL URL DIRECTLY ===\n\n";

// Get approver user
$approver = User::whereHas('roles', function($q) {
    $q->where('name', 'Approver');
})->first();

if (!$approver) {
    echo "❌ No Approver user found!\n";
    exit(1);
}

echo "Testing as: {$approver->name} ({$approver->email})\n";
echo str_repeat("=", 80) . "\n\n";

// Create request
$request = Request::create('/approvals', 'GET', ['tab' => 'pending']);
$request->setUserResolver(function () use ($approver) {
    return $approver;
});

try {
    // Process request through kernel
    $response = $kernel->handle($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Type: " . get_class($response) . "\n";
    echo "\n";
    
    if ($response->getStatusCode() === 200) {
        $content = $response->getContent();
        
        // Check if content contains the empty state message
        if (strpos($content, 'Antrian Kosong') !== false) {
            echo "❌ PROBLEM: Page shows 'Antrian Kosong' (empty queue)\n";
        } elseif (strpos($content, 'PO-') !== false) {
            echo "✅ SUCCESS: Page contains PO numbers\n";
            
            // Count how many POs are shown
            preg_match_all('/PO-\d{8}-\d{4}/', $content, $matches);
            $poCount = count($matches[0]);
            echo "   Found {$poCount} PO(s) in HTML\n";
            
            if ($poCount > 0) {
                echo "   PO Numbers: " . implode(', ', array_unique($matches[0])) . "\n";
            }
        } else {
            echo "⚠️ WARNING: Cannot determine page state\n";
        }
        
        // Check for errors in HTML
        if (strpos($content, 'error') !== false || strpos($content, 'Error') !== false) {
            echo "\n⚠️ WARNING: HTML contains error messages\n";
        }
        
        // Check if view is rendered
        if (strpos($content, 'Manajemen Persetujuan') !== false) {
            echo "\n✅ View rendered: Title found\n";
        } else {
            echo "\n❌ View NOT rendered: Title not found\n";
        }
        
        // Check for table
        if (strpos($content, 'Antrian Persetujuan') !== false) {
            echo "✅ Table section found\n";
        } else {
            echo "❌ Table section NOT found\n";
        }
        
    } else {
        echo "❌ ERROR: Non-200 response\n";
        echo "Response content:\n";
        echo $response->getContent() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
