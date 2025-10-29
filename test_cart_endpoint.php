<?php
/**
 * Test Cart Payment Endpoint
 * Upload file ini ke root folder VPS dan akses via browser
 */

echo "<pre>";
echo "================================================\n";
echo "🧪 Test Cart Payment Endpoint\n";
echo "================================================\n\n";

// Test if route exists
echo "1️⃣ Testing route existence...\n";
exec("php artisan route:list --name=cart.payment.ipaymu 2>&1", $output);
echo implode("\n", $output) . "\n\n";

// Test StudentAuthController exists
echo "2️⃣ Testing StudentAuthController...\n";
$controllerFile = __DIR__ . '/app/Http/Controllers/StudentAuthController.php';
if (file_exists($controllerFile)) {
    echo "  ✅ Controller file exists\n";
    
    // Check if processCartPaymentIpaymu method exists
    $content = file_get_contents($controllerFile);
    if (strpos($content, 'function processCartPaymentIpaymu') !== false) {
        echo "  ✅ Method processCartPaymentIpaymu exists\n";
    } else {
        echo "  ❌ Method processCartPaymentIpaymu NOT FOUND!\n";
    }
} else {
    echo "  ❌ Controller file NOT FOUND!\n";
}

echo "\n";

// Test IpaymuService exists
echo "3️⃣ Testing IpaymuService...\n";
$serviceFile = __DIR__ . '/app/Services/IpaymuService.php';
if (file_exists($serviceFile)) {
    echo "  ✅ IpaymuService file exists\n";
} else {
    echo "  ❌ IpaymuService file NOT FOUND!\n";
}

echo "\n";

// Test database connection
echo "4️⃣ Testing database connection...\n";
try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $pdo = DB::connection()->getPdo();
    echo "  ✅ Database connected\n";
    
    // Check student table
    $studentCount = DB::table('students')->count();
    echo "  ✅ Students table: $studentCount records\n";
    
    // Check setup_gateways
    $gateway = DB::table('setup_gateways')->first();
    if ($gateway) {
        echo "  ✅ Payment gateway configured\n";
        echo "  - iPaymu Active: " . ($gateway->ipaymu_is_active ? 'YES' : 'NO') . "\n";
        echo "  - iPaymu Mode: " . ($gateway->ipaymu_mode ?? 'N/A') . "\n";
    } else {
        echo "  ⚠️  No payment gateway configuration\n";
    }
    
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test actual endpoint with CURL
echo "5️⃣ Testing actual endpoint with CURL...\n";
$url = 'https://srx.sppqu.my.id/student/cart/payment/ipaymu';
$postData = [
    'cart_items' => json_encode([
        ['type' => 'bulanan', 'id' => 1, 'bill_name' => 'Test', 'amount' => 100000]
    ]),
    'total_amount' => 100000
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: <strong>$httpCode</strong>\n";
echo "  Response:\n";
echo "  " . str_replace("\n", "\n  ", substr($response, 0, 500)) . "\n";
if (strlen($response) > 500) {
    echo "  ... (truncated)\n";
}

echo "\n";
echo "================================================\n";
echo "📝 Next steps:\n";
echo "1. <a href='/check_error.php'>Check error log</a>\n";
echo "2. <a href='/clear_cache_vps.php'>Clear cache</a>\n";
echo "3. <a href='/student/cart'>Test in browser</a>\n";
echo "================================================\n";
echo "</pre>";

