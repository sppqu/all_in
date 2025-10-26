<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionTripayService
{
    protected $apiKey;
    protected $privateKey;
    protected $merchantCode;
    protected $baseUrl;

    public function __construct()
    {
        // Get Tripay config from environment or config
        $this->apiKey = config('tripay.api_key');
        $this->privateKey = config('tripay.private_key');
        $this->merchantCode = config('tripay.merchant_code');
        
        // Use sandbox URL
        $this->baseUrl = 'https://tripay.co.id/api-sandbox/';
    }

    /**
     * Get available payment channels
     */
    public function getPaymentChannels()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->baseUrl . 'merchant/payment-channel');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tripay get payment channels failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Tripay get payment channels error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create subscription payment transaction
     * 
     * Example callback_url: route('manage.tripay.callback')
     * Or: url('/manage/tripay/callback')
     */
    public function createSubscriptionPayment($data)
    {
        try {
            $merchantRef = 'SUB-' . $data['user_id'] . '-' . time();
            
            // Generate signature
            $signature = $this->generateSignature($merchantRef, $data['amount']);
            
            $payload = [
                'method' => $data['method'], // QRIS, BRIVA, BCAVA, etc
                'merchant_ref' => $merchantRef,
                'amount' => (int) $data['amount'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? '08123456789',
                'order_items' => [
                    [
                        'name' => $data['plan_name'],
                        'price' => (int) $data['amount'],
                        'quantity' => 1,
                    ]
                ],
                'return_url' => $data['return_url'],
                'callback_url' => $data['callback_url'],
                'expired_time' => (time() + (24 * 60 * 60)), // 24 hours
                'signature' => $signature
            ];

            Log::info('Tripay Subscription Payment Request', [
                'payload' => $payload,
                'merchant_ref' => $merchantRef
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . 'transaction/create', $payload);

            Log::info('Tripay Subscription Payment Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Return standardized response
                return [
                    'success' => true,
                    'merchant_ref' => $merchantRef,
                    'reference' => $responseData['data']['reference'] ?? null,
                    'checkout_url' => $responseData['data']['checkout_url'] ?? null,
                    'qr_url' => $responseData['data']['qr_url'] ?? null,
                    'payment_method' => $data['method'],
                    'amount' => $data['amount'],
                    'expired_time' => $responseData['data']['expired_time'] ?? null,
                    'data' => $responseData['data'] ?? null
                ];
            }

            $errorResponse = $response->json();
            $errorMessage = $errorResponse['message'] ?? $errorResponse['error'] ?? 'Failed to create transaction';
            
            Log::error('Tripay create subscription transaction failed', [
                'response' => $errorResponse,
                'status' => $response->status(),
                'error_message' => $errorMessage
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'error' => $errorResponse
            ];

        } catch (\Exception $e) {
            Log::error('Tripay create subscription transaction error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create addon payment transaction
     */
    public function createAddonPayment($data)
    {
        try {
            $merchantRef = 'ADDON-' . $data['user_id'] . '-' . $data['addon_id'] . '-' . time();
            
            // Generate signature
            $signature = $this->generateSignature($merchantRef, $data['amount']);
            
            $payload = [
                'method' => $data['method'], // QRIS, BRIVA, BCAVA, etc
                'merchant_ref' => $merchantRef,
                'amount' => (int) $data['amount'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? '08123456789',
                'order_items' => [
                    [
                        'name' => $data['addon_name'],
                        'price' => (int) $data['amount'],
                        'quantity' => 1,
                    ]
                ],
                'return_url' => $data['return_url'],
                'callback_url' => $data['callback_url'],
                'expired_time' => (time() + (24 * 60 * 60)), // 24 hours
                'signature' => $signature
            ];

            Log::info('Tripay Addon Payment Request', [
                'payload' => $payload,
                'merchant_ref' => $merchantRef
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . 'transaction/create', $payload);

            Log::info('Tripay Addon Payment Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'merchant_ref' => $merchantRef,
                    'reference' => $responseData['data']['reference'] ?? null,
                    'checkout_url' => $responseData['data']['checkout_url'] ?? null,
                    'qr_url' => $responseData['data']['qr_url'] ?? null,
                    'payment_method' => $data['method'],
                    'amount' => $data['amount'],
                    'expired_time' => $responseData['data']['expired_time'] ?? null,
                    'data' => $responseData['data'] ?? null
                ];
            }

            Log::error('Tripay create addon transaction failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create transaction',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Tripay create addon transaction error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback($data)
    {
        $callbackSignature = $data['signature'] ?? '';
        
        // Generate expected signature
        $expectedSignature = hash_hmac(
            'sha256',
            $data['reference'] . $data['status'],
            $this->privateKey
        );

        return $callbackSignature === $expectedSignature;
    }

    /**
     * Generate signature for transaction
     */
    private function generateSignature($merchantRef, $amount)
    {
        return hash_hmac(
            'sha256',
            $this->merchantCode . $merchantRef . $amount,
            $this->privateKey
        );
    }

    /**
     * Get transaction detail
     */
    public function getTransactionDetail($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->baseUrl . 'transaction/detail', [
                'reference' => $reference
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Tripay get transaction detail error: ' . $e->getMessage());
            return null;
        }
    }
}

