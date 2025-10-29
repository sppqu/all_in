<?php
/**
 * Test Route Availability
 */

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a test request
$request = Illuminate\Http\Request::create(
    '/student/cart/payment/ipaymu',
    'POST',
    [
        'cart_items' => json_encode([
            ['type' => 'bulanan', 'id' => 1, 'bill_name' => 'Test', 'amount' => 100000]
        ]),
        'total_amount' => 100000
    ]
);

// Set session data
$request->setLaravelSession(new \Illuminate\Session\Store(
    'test-session',
    new \Illuminate\Session\ArraySessionHandler(120)
));
$request->getSession()->put('student_id', 1);
$request->getSession()->put('is_student', true);

try {
    echo "Testing route: POST /student/cart/payment/ipaymu\n";
    echo "========================================\n\n";
    
    $response = $kernel->handle($request);
    
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content:\n";
    echo $response->getContent() . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response ?? null);

