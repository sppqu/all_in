<?php
/**
 * Test Cart Payment Endpoint
 */

// Simulate POST request to cart payment
$url = 'https://srx.sppqu.my.id/student/cart/payment/ipaymu';

$testData = [
    'cart_items' => json_encode([
        [
            'type' => 'bulanan',
            'id' => 1,
            'bill_name' => 'Test Tagihan',
            'amount' => 100000
        ]
    ]),
    'total_amount' => 100000
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIE, 'laravel_session=test'); // Add session cookie

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "\n=== Response ===\n";
echo $response;

