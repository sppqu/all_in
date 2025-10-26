<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SPMBTripayService
{
    protected $apiKey;
    protected $privateKey;
    protected $merchantCode;
    protected $baseUrl;
    protected $isSandbox;

    public function __construct()
    {
        try {
            $this->apiKey = config('tripay.api_key', '');
            $this->privateKey = config('tripay.private_key', '');
            $this->merchantCode = config('tripay.merchant_code', '');
            $this->baseUrl = config('tripay.base_url', 'https://tripay.co.id/api/');
            $this->isSandbox = config('tripay.is_sandbox', true);
            
            // For development/testing, allow empty credentials
            if (empty($this->apiKey) || $this->apiKey === 'your_tripay_api_key_here') {
                Log::warning('Tripay configuration is incomplete. Using mock data for development.');
                $this->apiKey = 'mock_api_key';
                $this->privateKey = 'mock_private_key';
                $this->merchantCode = 'mock_merchant_code';
            }
        } catch (\Exception $e) {
            Log::error('Tripay service initialization error: ' . $e->getMessage());
            // Use mock data as fallback
            $this->apiKey = 'mock_api_key';
            $this->privateKey = 'mock_private_key';
            $this->merchantCode = 'mock_merchant_code';
            $this->baseUrl = 'https://tripay.co.id/api/';
            $this->isSandbox = true;
        }
    }

    /**
     * Create SPMB registration fee payment (Step-2 QRIS)
     */
    public function createRegistrationFeePayment($registration)
    {
        $amount = \App\Helpers\WaveHelper::getStep2QrisFee();
        $paymentReference = 'QRIS-STEP2-' . time() . '-' . $registration->id;
        
        $data = [
            'method' => 'QRIS',
            'merchant_ref' => $paymentReference,
            'amount' => $amount,
            'customer_name' => $registration->name,
            'customer_phone' => $registration->phone,
            'order_items' => [
                [
                    'name' => 'Biaya QRIS Step-2 Pendaftaran (Default Rp 3.000 )',
                    'price' => $amount,
                    'quantity' => 1
                ]
            ],
            'return_url' => route('spmb.payment.success'),
            'callback_url' => route('spmb.payment.callback')
        ];

        return $this->createTransaction($data);
    }

    /**
     * Create SPMB fee payment
     */
    public function createSPMBFeePayment($registration)
    {
        $amount = config('tripay.spmb.spmb_fee');
        $paymentReference = 'SPMB-' . time() . '-' . $registration->id;
        
        $data = [
            'method' => 'QRIS',
            'merchant_ref' => $paymentReference,
            'amount' => $amount,
            'customer_name' => $registration->name,
            'customer_phone' => $registration->phone,
            'order_items' => [
                [
                    'name' => 'Biaya SPMB',
                    'price' => $amount,
                    'quantity' => 1
                ]
            ],
            'return_url' => route('spmb.payment.success'),
            'callback_url' => route('spmb.payment.callback')
        ];

        return $this->createTransaction($data);
    }

    /**
     * Create transaction
     */
    public function createTransaction($data)
    {
        try {
            // Check if using mock data
            if ($this->apiKey === 'mock_api_key') {
                return $this->createMockTransaction($data);
            }

            // Generate signature
            $signature = $this->generateSignature($data['merchant_ref'], $data['amount']);
            
            $payload = [
                'method' => $data['method'],
                'merchant_ref' => $data['merchant_ref'],
                'amount' => $data['amount'],
                'customer_name' => $data['customer_name'],
                'customer_email' => 'student@sppqu.com',
                'customer_phone' => $data['customer_phone'],
                'order_items' => $data['order_items'],
                'return_url' => $data['return_url'],
                'callback_url' => $data['callback_url'],
                'expired_time' => (time() + (24 * 60 * 60)), // 24 hours
                'signature' => $signature
            ];

            Log::info('SPMB Tripay transaction payload', [
                'payload' => $payload,
                'api_key_length' => strlen($this->apiKey),
                'base_url' => $this->baseUrl
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . 'transaction/create', $payload);

            Log::info('SPMB Tripay response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('SPMB Tripay successful response', $responseData);
                return $responseData;
            }

            Log::error('SPMB Tripay create transaction failed', [
                'payload' => $payload,
                'response' => $response->json(),
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SPMB Tripay create transaction error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return null;
        }
    }

    /**
     * Create mock transaction for development
     */
    private function createMockTransaction($data)
    {
        Log::info('SPMB Tripay using mock transaction for development');
        
        return [
            'success' => true,
            'data' => [
                'reference' => 'MOCK-' . time() . '-' . rand(1000, 9999),
                'merchant_ref' => $data['merchant_ref'],
                'checkout_url' => 'https://tripay.co.id/checkout/mock',
                'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                'amount' => $data['amount'],
                'status' => 'UNPAID',
                'expired_time' => time() + (24 * 60 * 60)
            ]
        ];
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

            Log::error('SPMB Tripay get transaction detail failed', [
                'reference' => $reference,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SPMB Tripay get transaction detail error', [
                'message' => $e->getMessage(),
                'reference' => $reference
            ]);
            return null;
        }
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback($data, $signature)
    {
        $expectedSignature = hash_hmac('sha256', 
            $data['merchant_ref'] . $data['amount'], 
            $this->privateKey
        );

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Generate signature for transaction
     */
    private function generateSignature($merchantRef, $amount)
    {
        $signature = hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey);
        
        Log::info('SPMB Tripay signature generation', [
            'merchant_code' => $this->merchantCode,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'private_key_length' => strlen($this->privateKey),
            'signature' => $signature
        ]);
        
        return $signature;
    }

    /**
     * Get payment method name
     */
    public function getPaymentMethodName($method)
    {
        $methods = config('tripay.payment_methods');
        return $methods[$method] ?? $method;
    }

    /**
     * Check if configuration is valid
     */
    public function isConfigured()
    {
        return !empty($this->apiKey) && !empty($this->privateKey) && !empty($this->merchantCode);
    }
}
