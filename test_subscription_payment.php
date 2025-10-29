<?php

/**
 * Test Subscription Payment Creation
 * 
 * This script tests subscription payment creation directly
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Test Subscription Payment Creation                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Get superadmin
$admin = DB::table('users')->where('role', 'superadmin')->first();

if (!$admin) {
    echo "❌ Superadmin not found!\n";
    exit(1);
}

// Get phone
$customerPhone = $admin->nomor_wa ?? $admin->phone ?? $admin->no_hp ?? '08123456789';

echo "Testing with:\n";
echo "  User ID: " . $admin->id . "\n";
echo "  Name: " . $admin->name . "\n";
echo "  Email: " . $admin->email . "\n";
echo "  Phone: " . $customerPhone . "\n";
echo "\n";

// Test payment creation
try {
    $ipaymu = new \App\Services\IpaymuService(true); // Use ENV config
    
    echo "Creating subscription payment...\n\n";
    
    $result = $ipaymu->createSubscriptionPayment([
        'user_id' => $admin->id,
        'method' => 'QRIS',
        'amount' => 189000,
        'plan_name' => 'SPPQU Subscription - 1 Bulan (TEST)',
        'customer_name' => $admin->name,
        'customer_email' => $admin->email,
        'customer_phone' => $customerPhone,
        'return_url' => url('/manage/subscription'),
        'callback_url' => url('/api/manage/ipaymu/callback')
    ]);
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "RESULT:\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    
    if ($result['success']) {
        echo "✅ SUCCESS!\n\n";
        echo "Reference ID: " . ($result['reference_id'] ?? 'N/A') . "\n";
        echo "Transaction ID: " . ($result['transaction_id'] ?? 'N/A') . "\n";
        echo "Payment URL: " . ($result['payment_url'] ?? 'N/A') . "\n";
        echo "QR String: " . (isset($result['qr_string']) ? 'YES (length: ' . strlen($result['qr_string']) . ')' : 'N/A') . "\n";
        echo "Expired: " . ($result['expired'] ?? 'N/A') . "\n";
        echo "\n";
        echo "Full result:\n";
        print_r($result);
    } else {
        echo "❌ FAILED!\n\n";
        echo "Message: " . ($result['message'] ?? 'Unknown error') . "\n";
        echo "\n";
        echo "Full result:\n";
        print_r($result);
    }
    
} catch (\Exception $e) {
    echo "❌ EXCEPTION!\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "If successful, the problem is NOT in iPaymu API.\n";
echo "Check:\n";
echo "  1. Browser console (F12) for JavaScript errors\n";
echo "  2. Network tab for actual HTTP status code\n";
echo "  3. CSRF token issue (419 disguised as 406?)\n";
echo "  4. Route middleware issue\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

