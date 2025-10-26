<?php
/**
 * Test Tripay Callback Endpoint
 * Upload file ini ke root project VPS dan akses via browser
 * URL: https://srx.sppqu.my.id/test_tripay_callback.php
 */

// Simulate Tripay callback
$url = 'https://srx.sppqu.my.id/manage/tripay/callback';

$data = [
    'reference' => 'TEST-' . time(),
    'merchant_ref' => 'SUB-1-' . time(),
    'payment_method' => 'QRIS',
    'payment_name' => 'QRIS',
    'customer_name' => 'Test User',
    'customer_email' => 'test@example.com',
    'customer_phone' => '08123456789',
    'callback_virtual_account' => null,
    'callback_va_number' => null,
    'callback_biller_code' => null,
    'callback_bill_key' => null,
    'order_items' => [],
    'amount' => 100000,
    'fee_merchant' => 1500,
    'fee_customer' => 0,
    'total_fee' => 1500,
    'amount_received' => 98500,
    'is_closed_payment' => 0,
    'status' => 'PAID',
    'paid_at' => time(),
    'note' => null
];

// Signature (dummy for test)
$data['signature'] = 'test_signature_' . md5(json_encode($data));

echo "<h2>🧪 Test Tripay Callback</h2>";
echo "<p><strong>Target URL:</strong> $url</p>";
echo "<p><strong>Method:</strong> POST</p>";
echo "<hr>";

// Test 1: GET Request (should return status ok)
echo "<h3>Test 1: GET Request</h3>";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
if ($httpCode == 200) {
    echo "<p style='color: green;'>✅ GET Request berhasil!</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ GET Request gagal!</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<hr>";

// Test 2: POST Request (simulate Tripay callback)
echo "<h3>Test 2: POST Request (Simulate Tripay)</h3>";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Callback-Event: payment_status'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
if ($httpCode == 200) {
    echo "<p style='color: green;'>✅ POST Request berhasil!</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} elseif ($httpCode == 419) {
    echo "<p style='color: red;'>❌ Error 419: CSRF Token Mismatch</p>";
    echo "<p>Solusi:</p>";
    echo "<ol>";
    echo "<li>Pastikan file <code>app/Http/Middleware/VerifyCsrfToken.php</code> sudah terupdate</li>";
    echo "<li>Jalankan: <code>php artisan optimize:clear</code></li>";
    echo "<li>Jalankan: <code>php artisan route:clear</code></li>";
    echo "<li>Restart web server (nginx/apache)</li>";
    echo "</ol>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} else {
    echo "<p style='color: orange;'>⚠️ HTTP Code: $httpCode</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<hr>";
echo "<h3>📋 Checklist</h3>";
echo "<ul>";
echo "<li>✅ File <code>routes/web.php</code> sudah ada route callback</li>";
echo "<li>✅ File <code>app/Http/Middleware/VerifyCsrfToken.php</code> sudah exclude callback</li>";
echo "<li>✅ File <code>app/Http/Controllers/TripayCallbackController.php</code> exists</li>";
echo "<li>⚠️ Cache sudah di-clear?</li>";
echo "<li>⚠️ Web server sudah di-restart?</li>";
echo "</ul>";

echo "<hr>";
echo "<p><small>Generated at " . date('Y-m-d H:i:s') . "</small></p>";

