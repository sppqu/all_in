<?php

/**
 * Test Subscription iPaymu Callback Simulation
 * 
 * This script simulates iPaymu callback for testing
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║       Test Subscription iPaymu Callback Simulation           ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Get latest pending subscription
$subscription = DB::table('subscriptions')
    ->where('status', 'pending')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$subscription) {
    echo "❌ No pending subscription found!\n";
    echo "\n";
    echo "Create a subscription first:\n";
    echo "  1. Go to browser\n";
    echo "  2. Choose subscription plan\n";
    echo "  3. Create payment\n";
    echo "  4. Then run this script\n";
    echo "\n";
    exit(1);
}

echo "Found pending subscription:\n";
echo "  ID: " . $subscription->id . "\n";
echo "  Transaction ID: " . $subscription->transaction_id . "\n";
echo "  Plan: " . $subscription->plan_name . "\n";
echo "  Amount: " . number_format($subscription->amount, 0, ',', '.') . "\n";
echo "  Status: " . $subscription->status . "\n";
echo "\n";

// Simulate iPaymu callback payload
$callbackPayload = [
    'status' => 'berhasil',
    'status_code' => 1,
    'trx_id' => $subscription->payment_reference ?? '27140683',
    'reference_id' => $subscription->transaction_id,
    'amount' => $subscription->amount,
    'via' => 'qris'
];

echo "Simulating iPaymu callback with payload:\n";
print_r($callbackPayload);
echo "\n";

// Call callback handler
try {
    $controller = new \App\Http\Controllers\IpaymuCallbackController();
    
    // Simulate callback request
    $request = new \Illuminate\Http\Request();
    $request->merge($callbackPayload);
    
    // Call callback
    echo "Calling callback handler...\n\n";
    
    // Directly call the handler method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('handleSubscriptionCallback');
    $method->setAccessible(true);
    
    $method->invoke(
        $controller,
        $callbackPayload['reference_id'],
        $callbackPayload['status'],
        $callbackPayload['status_code'],
        $callbackPayload['trx_id'],
        $callbackPayload['amount']
    );
    
    echo "✅ Callback executed!\n\n";
    
    // Check subscription status after callback
    $updatedSubscription = DB::table('subscriptions')
        ->where('id', $subscription->id)
        ->first();
    
    echo "Updated subscription status:\n";
    echo "  ID: " . $updatedSubscription->id . "\n";
    echo "  Status: " . $updatedSubscription->status . "\n";
    echo "  Payment Reference: " . ($updatedSubscription->payment_reference ?? 'NULL') . "\n";
    echo "  Starts At: " . ($updatedSubscription->starts_at ?? 'NULL') . "\n";
    echo "  Expires At: " . ($updatedSubscription->expires_at ?? 'NULL') . "\n";
    echo "\n";
    
    if ($updatedSubscription->status === 'active') {
        echo "✅ SUCCESS! Subscription is now ACTIVE!\n";
        echo "\n";
        echo "You can now test in browser:\n";
        echo "  1. Go to Berlangganan page\n";
        echo "  2. Should see active subscription\n";
    } elseif ($updatedSubscription->status === 'paid') {
        echo "✅ SUCCESS! Subscription is PAID (will be activated)!\n";
    } else {
        echo "⚠️  Status: " . $updatedSubscription->status . "\n";
        echo "   Expected: active or paid\n";
    }
    
    // Check invoice
    $invoice = DB::table('subscription_invoices')
        ->where('subscription_id', $subscription->id)
        ->first();
    
    if ($invoice) {
        echo "\n";
        echo "Invoice status:\n";
        echo "  Payment Status: " . $invoice->payment_status . "\n";
        echo "  Paid At: " . ($invoice->paid_at ?? 'NULL') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test complete!\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

