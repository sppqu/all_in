<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\IpaymuCallbackController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// ============================================================================
// IPAYMU SUBSCRIPTION & ADDON CALLBACK - NO CSRF, NO AUTH
// ============================================================================
Route::any('/manage/ipaymu/callback', [IpaymuCallbackController::class, 'handle'])
    ->name('api.ipaymu.callback');

// ============================================================================
// TRIPAY CALLBACK REMOVED - Now using iPaymu only
// ============================================================================

// Legacy Tripay routes
Route::post('/tripay/callback', [CallbackController::class, 'tripayCallback']);
Route::post('/payment/callback', [CallbackController::class, 'tripayCallback']);
Route::post('/callback', [CallbackController::class, 'tripayCallback']);

// Test routes
Route::get('/test', function() {
    return response()->json([
        'success' => true,
        'message' => 'API endpoint accessible',
        'timestamp' => now()
    ]);
});

Route::post('/test', [CallbackController::class, 'testCallback']);

// Midtrans tabungan test route - REMOVED
/*
Route::post('/midtrans/tabungan-test', function(Request $request) {
    try {
        return response()->json([
            'success' => true,
            'message' => 'Tabungan test endpoint working',
            'amount' => $request->input('amount', 100000),
            'description' => $request->input('description', 'Test Setor Tabungan via Midtrans')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});
*/

// Ultra simple callback routes
Route::post('/ultra-callback', function() {
    return 'API-OK';
});

Route::post('/no-csrf', function() {
    return 'API-NO-CSRF-OK';
});

// Super simple API callback routes
Route::post('/api-callback', function() {
    return 'API-CALLBACK-OK';
});

Route::post('/api-webhook', function() {
    return 'API-WEBHOOK-OK';
});

// Midtrans routes REMOVED - using iPaymu now
// All Midtrans test routes have been commented out

/*
// Test route untuk Midtrans configuration
Route::get('/midtrans/config-test', function() {
    try {
        $config = \App\Helpers\MidtransHelper::getConfig();
        $isConfigured = \App\Helpers\MidtransHelper::isConfigured();
        
        return response()->json([
            'success' => true,
            'is_configured' => $isConfigured,
            'config' => [
                'mode' => $config['mode'] ?? 'unknown',
                'is_production' => $config['is_production'] ?? false,
                'server_key_length' => strlen($config['server_key'] ?? ''),
                'client_key_length' => strlen($config['client_key'] ?? ''),
                'merchant_id_length' => strlen($config['merchant_id'] ?? '')
            ],
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
});

// Test route untuk Midtrans payment creation
Route::post('/midtrans/payment-test', function(Request $request) {
    try {
        $midtransHelper = new \App\Helpers\MidtransHelper();
        
        $testData = [
            'transaction_details' => [
                'order_id' => 'TEST-' . time(),
                'gross_amount' => 10000
            ],
            'item_details' => [
                [
                    'id' => 'test_item',
                    'price' => 10000,
                    'quantity' => 1,
                    'name' => 'Test Payment'
                ]
            ],
            'customer_details' => [
                'first_name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '08123456789'
            ],
            'enabled_payments' => ['credit_card', 'bca_va', 'bni_va', 'bri_va'],
            'callbacks' => [
                'finish' => url('/midtrans/finish'),
                'error' => url('/midtrans/error'),
                'pending' => url('/midtrans/pending')
            ],
            'notification_url' => request()->getSchemeAndHttpHost() . '/api/midtrans/webhook'
        ];
        
        $response = $midtransHelper->createSnapToken($testData);
        
        if (!$response['success']) {
            // Try SDK method as fallback
            $response = $midtransHelper->createSnapTokenWithSDK($testData);
        }
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Payment test failed: ' . $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
});

// Test route untuk cart payment (API version without CSRF)
Route::post('/midtrans/cart-payment-test', function(Request $request) {
    try {
        // Debug: Log all request data
        Log::info('API Cart payment request received', [
            'all_data' => $request->all(),
            'cart_items_type' => gettype($request->cart_items),
            'cart_items_value' => $request->cart_items
        ]);

        // Handle cart_items as JSON string or array
        $cartItems = $request->cart_items;
        if (is_string($cartItems)) {
            $cartItems = json_decode($cartItems, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'error' => json_last_error_msg(),
                    'cart_items' => $cartItems
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Format cart_items tidak valid: ' . json_last_error_msg()
                ], 400);
            }
        }
        
        // Validate cart_items structure
        if (!is_array($cartItems) || empty($cartItems)) {
            Log::error('Cart items validation failed', [
                'cart_items' => $cartItems
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Cart items harus berupa array dan tidak boleh kosong'
            ], 400);
        }
        
        // Get student ID from request
        $studentId = $request->input('student_id');
        if (!$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'Student ID tidak ditemukan'
            ], 400);
        }
        
        // Use improved MidtransHelper
        $midtransHelper = new \App\Helpers\MidtransHelper();
        
        // Check if Midtrans is configured
        if (!\App\Helpers\MidtransHelper::isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway tidak dikonfigurasi'
            ], 400);
        }
        
        // Calculate total amount and prepare items
        $totalAmount = 0;
        $items = [];
        
        foreach ($cartItems as $item) {
            // Normalize data structure
            if (isset($item['type']) && !isset($item['bill_type'])) {
                $item['bill_type'] = $item['type'];
            }
            if (isset($item['id']) && !isset($item['bill_id'])) {
                $item['bill_id'] = $item['id'];
            }
            
            // Convert amount to numeric
            $amount = is_numeric($item['amount']) ? $item['amount'] : (float) str_replace(['Rp ', ',', '.'], '', $item['amount']);
            $totalAmount += $amount;
            
            $items[] = [
                'id' => $item['bill_type'] . '_' . $item['bill_id'],
                'price' => $amount,
                'quantity' => 1,
                'name' => $item['name'] ?? 'Pembayaran ' . ucfirst($item['bill_type'])
            ];
        }
        
        // Prepare Midtrans parameters
        $params = [
            'transaction_details' => [
                'order_id' => 'CART-' . $studentId . '-' . time(),
                'gross_amount' => $totalAmount
            ],
            'customer_details' => [
                'first_name' => 'Student ' . $studentId,
                'email' => 'student@example.com',
                'phone' => '08123456789'
            ],
            'item_details' => $items,
            'enabled_payments' => [
                'credit_card', 'bca_va', 'bni_va', 'bri_va', 'mandiri_clickpay',
                'gopay', 'indomaret', 'danamon_online', 'akulaku'
            ],
            'callbacks' => [
                'finish' => url('/callback/midtrans'),
                'error' => url('/callback/midtrans'),
                'pending' => url('/callback/midtrans')
            ],
            'notification_url' => request()->getSchemeAndHttpHost() . '/api/midtrans/webhook'
        ];
        
        // Create snap token using improved helper with fallback
        $response = $midtransHelper->createSnapToken($params);
        
        if (!$response['success']) {
            // Try SDK method as fallback
            $response = $midtransHelper->createSnapTokenWithSDK($params);
        }
        
        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token pembayaran: ' . $response['message']
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'snap_token' => $response['data']['token'],
            'message' => 'Token pembayaran berhasil dibuat'
        ]);
        
    } catch (\Exception $e) {
        Log::error('API Cart payment error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage()
        ], 500);
    }
});
*/ 
