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
            // Ambil langsung dari .env
            $this->apiKey = env('TRIPAY_API_KEY', '');
            $this->privateKey = env('TRIPAY_PRIVATE_KEY', '');
            $this->merchantCode = env('TRIPAY_MERCHANT_CODE', '');
            $this->isSandbox = env('TRIPAY_SANDBOX', true);
            
            // Set base URL berdasarkan sandbox mode
            if ($this->isSandbox) {
                $this->baseUrl = 'https://tripay.co.id/api-sandbox/';
            } else {
                $this->baseUrl = 'https://tripay.co.id/api/';
            }
            
            Log::info('SPMB Tripay Service initialized', [
                'api_key_set' => !empty($this->apiKey) ? 'YES' : 'NO',
                'api_key_length' => strlen($this->apiKey),
                'merchant_code' => $this->merchantCode,
                'is_sandbox' => $this->isSandbox ? 'YES' : 'NO',
                'base_url' => $this->baseUrl
            ]);
            
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
            $this->baseUrl = 'https://tripay.co.id/api-sandbox/';
            $this->isSandbox = true;
        }
    }

    /**
     * Create SPMB registration fee payment (Step-2 QRIS)
     */
    public function createRegistrationFeePayment($registration)
    {
        $amount = (int) \App\Helpers\WaveHelper::getStep2QrisFee(); // Cast to integer
        $paymentReference = 'QRIS-STEP2-' . time() . '-' . $registration->id;
        
        Log::info('Creating SPMB Step-2 QRIS payment', [
            'amount' => $amount,
            'payment_reference' => $paymentReference,
            'registration_id' => $registration->id,
            'customer_name' => $registration->name
        ]);
        
        $data = [
            'method' => 'QRIS',
            'merchant_ref' => $paymentReference,
            'amount' => $amount,
            'customer_name' => $registration->name,
            'customer_phone' => $registration->phone,
            'order_items' => [
                [
                    'name' => 'Biaya QRIS Step-2 Pendaftaran (Default Rp 3.000)',
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

            // Cast amount to integer before signature generation
            $amount = (int) $data['amount'];
            
            // Generate signature
            $signature = $this->generateSignature($data['merchant_ref'], $amount);
            
            $payload = [
                'method' => $data['method'],
                'merchant_ref' => $data['merchant_ref'],
                'amount' => $amount,
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
        // Cast amount to integer before signature generation (IMPORTANT!)
        $amount = (int) $amount;
        
        $signature = hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey);
        
        Log::info('SPMB Tripay signature generation', [
            'merchant_code' => $this->merchantCode,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'amount_type' => gettype($amount),
            'private_key_length' => strlen($this->privateKey),
            'signature' => $signature,
            'raw_string' => $this->merchantCode . $merchantRef . $amount
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
