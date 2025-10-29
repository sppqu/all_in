<?php

/**
 * Test iPaymu API Direct
 * 
 * This script tests iPaymu API directly with your credentials
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              iPaymu API Direct Test                           ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Get credentials from ENV
$va = env('IPAYMU_VA', '');
$apiKey = env('IPAYMU_API_KEY', '');
$sandbox = env('IPAYMU_SANDBOX', true);

echo "📋 Configuration from ENV:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "VA: " . (!empty($va) ? "✅ " . substr($va, 0, 10) . "..." . substr($va, -4) : "❌ EMPTY") . "\n";
echo "API Key: " . (!empty($apiKey) ? "✅ " . substr($apiKey, 0, 15) . "..." . substr($apiKey, -5) : "❌ EMPTY") . "\n";
echo "Mode: " . ($sandbox ? "🧪 SANDBOX" : "🚀 PRODUCTION") . "\n";

if (empty($va) || empty($apiKey)) {
    echo "\n❌ ERROR: VA or API Key is empty!\n";
    echo "Please set in .env file:\n";
    echo "IPAYMU_VA=your_va_here\n";
    echo "IPAYMU_API_KEY=your_api_key_here\n";
    echo "IPAYMU_SANDBOX=false\n";
    exit(1);
}

$baseUrl = $sandbox 
    ? 'https://sandbox.ipaymu.com/api/v2/'
    : 'https://my.ipaymu.com/api/v2/';

echo "Base URL: " . $baseUrl . "\n";
echo "\n";

// Test 1: Check Balance
echo "🧪 Test 1: Check Balance (GET /balance)\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $timestamp = time();
    
    // For GET requests, signature format: GET:VA:BODYHASH:APIKEY
    // Body is empty for GET
    $bodyHash = strtolower(hash('sha256', ''));
    $stringToSign = 'GET:' . $va . ':' . $bodyHash . ':' . $apiKey;
    $signature = hash_hmac('sha256', $stringToSign, $apiKey);
    
    echo "Timestamp: " . $timestamp . "\n";
    echo "Signature: " . substr($signature, 0, 20) . "...\n";
    echo "\n";
    
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'signature' => $signature,
        'va' => $va,
        'timestamp' => (string) $timestamp
    ])->get($baseUrl . 'balance');
    
    echo "Status Code: " . $response->status() . "\n";
    echo "Response:\n";
    echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
    if ($response->status() === 200) {
        echo "\n✅ Test 1 PASSED: Credentials are valid!\n";
        $balanceTest = true;
    } else {
        echo "\n❌ Test 1 FAILED: Status " . $response->status() . "\n";
        $balanceTest = false;
    }
} catch (\Exception $e) {
    echo "\n❌ Test 1 ERROR: " . $e->getMessage() . "\n";
    $balanceTest = false;
}

echo "\n";

// Test 2: Create Payment Transaction
echo "🧪 Test 2: Create Payment Transaction (POST /payment/direct)\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $timestamp = time();
    
    $bodyParams = [
        'name' => 'Test User',
        'phone' => '08123456789',
        'email' => 'test@example.com',
        'amount' => 3000,
        'notifyUrl' => 'https://yoursite.com/api/callback',
        'returnUrl' => 'https://yoursite.com/success',
        'cancelUrl' => 'https://yoursite.com/cancel',
        'referenceId' => 'TEST-' . $timestamp,
        'buyerName' => 'Test User',
        'buyerPhone' => '08123456789',
        'buyerEmail' => 'test@example.com',
        'paymentMethod' => 'qris',
        'paymentChannel' => 'qris',
        'product' => ['Test Product'],
        'qty' => [1],
        'price' => [3000],
        'weight' => [1],
        'width' => [1],
        'height' => [1],
        'length' => [1],
        'deliveryArea' => '76111',
        'deliveryAddress' => 'Jl Test'
    ];
    
    $bodyJson = json_encode($bodyParams, JSON_UNESCAPED_SLASHES);
    
    echo "Body JSON:\n";
    echo $bodyJson . "\n\n";
    
    // Signature format: POST:VA:BODYHASH:APIKEY
    $bodyHash = strtolower(hash('sha256', $bodyJson));
    $stringToSign = 'POST:' . $va . ':' . $bodyHash . ':' . $apiKey;
    $signature = hash_hmac('sha256', $stringToSign, $apiKey);
    
    echo "Signature String Length: " . strlen($stringToSign) . "\n";
    echo "Signature: " . substr($signature, 0, 20) . "...\n";
    echo "\n";
    
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'signature' => $signature,
        'va' => $va,
        'timestamp' => (string) $timestamp
    ])->post($baseUrl . 'payment/direct', $bodyParams);
    
    echo "Status Code: " . $response->status() . "\n";
    echo "Response:\n";
    echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
    if ($response->status() === 200) {
        echo "\n✅ Test 2 PASSED: Payment created successfully!\n";
        $paymentTest = true;
    } else if ($response->status() === 401) {
        echo "\n❌ Test 2 FAILED: 401 Unauthorized\n";
        echo "This means:\n";
        echo "- VA or API Key is incorrect\n";
        echo "- Signature generation is wrong\n";
        echo "- Using wrong environment (sandbox vs production)\n";
        $paymentTest = false;
    } else {
        echo "\n❌ Test 2 FAILED: Status " . $response->status() . "\n";
        $paymentTest = false;
    }
} catch (\Exception $e) {
    echo "\n❌ Test 2 ERROR: " . $e->getMessage() . "\n";
    $paymentTest = false;
}

echo "\n";

// Summary
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     Test Summary                              ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Test 1 (Check Balance): " . ($balanceTest ? "✅ PASSED" : "❌ FAILED") . "\n";
echo "Test 2 (Create Payment): " . ($paymentTest ? "✅ PASSED" : "❌ FAILED") . "\n";

echo "\n";

if ($balanceTest && $paymentTest) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "Your iPaymu credentials are working correctly.\n";
    echo "The issue might be in the application code, not the credentials.\n";
} else if ($balanceTest && !$paymentTest) {
    echo "⚠️  PARTIAL SUCCESS\n";
    echo "Balance check works but payment creation failed.\n";
    echo "This suggests the credentials are correct but there might be:\n";
    echo "- Issue with payment request format\n";
    echo "- Signature generation difference between GET and POST\n";
} else if (!$balanceTest && !$paymentTest) {
    echo "❌ ALL TESTS FAILED\n";
    echo "Your iPaymu credentials appear to be invalid.\n";
    echo "\n";
    echo "Please verify:\n";
    echo "1. Login to iPaymu dashboard: " . ($sandbox ? "https://sandbox.ipaymu.com" : "https://my.ipaymu.com") . "\n";
    echo "2. Go to Settings → API Configuration\n";
    echo "3. Copy the correct VA and API Key\n";
    echo "4. Update your .env file:\n";
    echo "   IPAYMU_VA=your_correct_va\n";
    echo "   IPAYMU_API_KEY=your_correct_api_key\n";
    echo "   IPAYMU_SANDBOX=" . ($sandbox ? 'true' : 'false') . "\n";
    echo "5. Clear config cache: php artisan config:clear\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

