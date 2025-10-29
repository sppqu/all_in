<?php

/**
 * Test SPMB Payment Creation
 * 
 * This script tests SPMB payment with various scenarios
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              Test SPMB Payment Creation                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Check iPaymu mode
$isSandbox = env('IPAYMU_SANDBOX', 'true') === 'true';
$ipaymuVa = env('IPAYMU_VA');
$ipaymuKey = env('IPAYMU_API_KEY');

echo "iPaymu Configuration:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "Mode: " . ($isSandbox ? "🟡 SANDBOX (Testing)" : "🔴 PRODUCTION (Live)") . "\n";
echo "VA: " . ($ipaymuVa ? substr($ipaymuVa, 0, 10) . '...' : 'NOT SET') . "\n";
echo "API Key: " . ($ipaymuKey ? substr($ipaymuKey, 0, 20) . '...' : 'NOT SET') . "\n";
echo "\n";

if (!$isSandbox) {
    echo "⚠️  WARNING: Running in PRODUCTION mode!\n";
    echo "   Real transactions will be created.\n";
    echo "   Recommendation: Switch to SANDBOX for testing.\n";
    echo "\n";
    echo "   To switch to SANDBOX:\n";
    echo "   1. Edit .env file\n";
    echo "   2. Set IPAYMU_SANDBOX=true\n";
    echo "   3. Restart PHP: systemctl restart php8.2-fpm\n";
    echo "\n";
}

// Get or create test registration
$registration = DB::table('s_p_m_b_registrations')->latest()->first();

if (!$registration) {
    echo "Creating test registration...\n";
    
    $regId = DB::table('s_p_m_b_registrations')->insertGetId([
        'name' => 'Ahmad Susanto', // Longer name to avoid suspicious
        'phone' => '082225855859',
        'email' => 'ahmad.susanto@gmail.com', // More realistic email
        'password' => bcrypt('password123'),
        'step' => 2,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $registration = DB::table('s_p_m_b_registrations')->find($regId);
    echo "✅ Test registration created (ID: $regId)\n\n";
}

echo "Test Registration Data:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "ID: " . $registration->id . "\n";
echo "Name: " . $registration->name . "\n";
echo "Phone: " . $registration->phone . "\n";
echo "Email: " . ($registration->email ?? 'NULL') . "\n";
echo "Step: " . $registration->step . "\n";
echo "\n";

// Test scenarios
$scenarios = [
    [
        'name' => 'Test 1: Original Data',
        'customer_name' => $registration->name,
        'customer_phone' => $registration->phone,
        'customer_email' => $registration->email ?? 'spmb@sppqu.com',
    ],
    [
        'name' => 'Test 2: With Normalized Email',
        'customer_name' => $registration->name,
        'customer_phone' => $registration->phone,
        'customer_email' => 'spmb' . substr($registration->phone, -6) . '@pendaftar.sppqu.com',
    ],
    [
        'name' => 'Test 3: Realistic User Data',
        'customer_name' => 'Ahmad Susanto Wijaya',
        'customer_phone' => '081234567890',
        'customer_email' => 'ahmad.wijaya@gmail.com',
    ],
];

foreach ($scenarios as $index => $scenario) {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo $scenario['name'] . "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Data:\n";
    echo "  Name: " . $scenario['customer_name'] . "\n";
    echo "  Phone: " . $scenario['customer_phone'] . "\n";
    echo "  Email: " . $scenario['customer_email'] . "\n";
    echo "\n";
    
    try {
        $ipaymu = new \App\Services\IpaymuService(true); // Use ENV config
        
        $result = $ipaymu->createSPMBPayment([
            'registration_id' => $registration->id,
            'amount' => 3000,
            'method' => 'qris',
            'product_name' => 'Step 2 Registration Fee',
            'customer_name' => $scenario['customer_name'],
            'customer_phone' => $scenario['customer_phone'],
            'customer_email' => $scenario['customer_email'],
            'return_url' => url('/spmb/payment/success'),
            'callback_url' => url('/api/manage/ipaymu/callback')
        ]);
        
        if ($result['success']) {
            echo "✅ SUCCESS!\n";
            echo "   Reference ID: " . $result['reference_id'] . "\n";
            echo "   Transaction ID: " . ($result['transaction_id'] ?? 'N/A') . "\n";
            echo "   QR String: " . (isset($result['qr_string']) ? 'YES (' . strlen($result['qr_string']) . ' chars)' : 'NO') . "\n";
            echo "   Payment URL: " . ($result['payment_url'] ?? 'N/A') . "\n";
            echo "\n";
            
            // Success - no need to test other scenarios
            echo "✅ Payment creation successful! Stopping here.\n";
            break;
        } else {
            echo "❌ FAILED!\n";
            echo "   Message: " . $result['message'] . "\n";
            echo "\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ EXCEPTION!\n";
        echo "   Error: " . $e->getMessage() . "\n";
        echo "\n";
    }
    
    if ($index < count($scenarios) - 1) {
        echo "Trying next scenario...\n\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Recommendations:\n";
echo "═══════════════════════════════════════════════════════════════\n";

if (!$isSandbox) {
    echo "1. ⚠️ SWITCH TO SANDBOX MODE for testing:\n";
    echo "   - Edit .env: IPAYMU_SANDBOX=true\n";
    echo "   - Restart: systemctl restart php8.2-fpm\n";
    echo "\n";
}

echo "2. 📝 Use REALISTIC data:\n";
echo "   - Name: Min 5 chars, looks real (e.g., 'Ahmad Susanto')\n";
echo "   - Email: Not generic (e.g., 'user@gmail.com')\n";
echo "   - Phone: Real format (e.g., '081234567890')\n";
echo "\n";

echo "3. 🔍 Check logs:\n";
echo "   tail -f storage/logs/laravel.log\n";
echo "\n";

echo "4. 📞 Contact iPaymu support if issue persists:\n";
echo "   - Error 406 'Suspicious buyer' might need whitelist\n";
echo "   - Provide: VA number, error details\n";
echo "\n";

