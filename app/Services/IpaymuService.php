<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpaymuService
{
    protected $va;
    protected $apiKey;
    protected $baseUrl;
    protected $isSandbox;

    public function __construct()
    {
        // Ambil kredensial dari .env
        $this->va = env('IPAYMU_VA', '');
        $this->apiKey = env('IPAYMU_API_KEY', '');
        $this->isSandbox = env('IPAYMU_SANDBOX', true);
        
        // Set base URL berdasarkan sandbox mode
        if ($this->isSandbox) {
            $this->baseUrl = 'https://sandbox.ipaymu.com/api/v2/';
        } else {
            $this->baseUrl = 'https://my.ipaymu.com/api/v2/';
        }
        
        Log::info('iPaymu Service initialized', [
            'va_set' => !empty($this->va) ? 'YES' : 'NO',
            'api_key_set' => !empty($this->apiKey) ? 'YES' : 'NO',
            'is_sandbox' => $this->isSandbox ? 'YES' : 'NO',
            'base_url' => $this->baseUrl
        ]);
    }

    /**
     * Generate signature for iPaymu
     * Signature = HMAC_SHA256(VA + ApiKey + RequestBody)
     */
    private function generateSignature($bodyParams)
    {
        $jsonBody = json_encode($bodyParams, JSON_UNESCAPED_SLASHES);
        $requestBody = strtolower(hash('sha256', $jsonBody));
        $stringToSign = 'POST:' . $this->va . ':' . $requestBody . ':' . $this->apiKey;
        
        $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
        
        Log::info('iPaymu Signature Generated', [
            'va' => $this->va,
            'body_hash' => $requestBody,
            'signature' => $signature
        ]);
        
        return $signature;
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods()
    {
        try {
            $signature = $this->generateSignature([]);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => now()->timestamp
            ])->post($this->baseUrl . 'payment/direct');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('iPaymu get payment methods failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('iPaymu get payment methods error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create subscription payment transaction
     */
    public function createSubscriptionPayment($data)
    {
        try {
            $referenceId = 'SUB-' . $data['user_id'] . '-' . time();
            $amount = (int) $data['amount'];
            
            $bodyParams = [
                'name' => $data['customer_name'],
                'phone' => $data['customer_phone'] ?? '08123456789',
                'email' => $data['customer_email'],
                'amount' => $amount,
                'notifyUrl' => $data['callback_url'],
                'returnUrl' => $data['return_url'],
                'cancelUrl' => $data['return_url'],
                'referenceId' => $referenceId,
                'buyerName' => $data['customer_name'],
                'buyerPhone' => $data['customer_phone'] ?? '08123456789',
                'buyerEmail' => $data['customer_email'],
                'paymentMethod' => $this->mapPaymentMethod($data['method'] ?? 'va'),
                'paymentChannel' => $this->mapPaymentChannel($data['method'] ?? 'va'),
                'product' => [
                    $data['plan_name']
                ],
                'qty' => [1],
                'price' => [$amount],
                'weight' => [1],
                'width' => [1],
                'height' => [1],
                'length' => [1],
                'deliveryArea' => '76111',
                'deliveryAddress' => 'Jl. Payment Gateway'
            ];

            $signature = $this->generateSignature($bodyParams);

            Log::info('iPaymu Subscription Payment Request', [
                'body_params' => $bodyParams,
                'reference_id' => $referenceId
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => now()->timestamp
            ])->post($this->baseUrl . 'payment/direct', $bodyParams);

            Log::info('iPaymu Subscription Payment Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if response is success
                if ($responseData['Status'] == 200) {
                    return [
                        'success' => true,
                        'reference_id' => $referenceId,
                        'session_id' => $responseData['Data']['SessionID'] ?? null,
                        'transaction_id' => $responseData['Data']['TransactionId'] ?? null,
                        'payment_url' => $responseData['Data']['Url'] ?? null,
                        'payment_no' => $responseData['Data']['PaymentNo'] ?? null,
                        'payment_name' => $responseData['Data']['PaymentName'] ?? null,
                        'payment_channel' => $responseData['Data']['PaymentChannel'] ?? null,
                        'va_number' => $responseData['Data']['Via'] ?? null,
                        'qr_string' => $responseData['Data']['QrString'] ?? null,
                        'expired' => $responseData['Data']['Expired'] ?? null,
                        'message' => $responseData['Message'] ?? 'Payment created successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $responseData['Message'] ?? 'Failed to create payment'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to iPaymu: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('iPaymu Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create addon payment transaction
     */
    public function createAddonPayment($data)
    {
        try {
            $referenceId = 'ADDON-' . $data['user_id'] . '-' . $data['addon_id'] . '-' . time();
            $amount = (int) $data['amount'];
            
            $bodyParams = [
                'name' => $data['customer_name'],
                'phone' => $data['customer_phone'] ?? '08123456789',
                'email' => $data['customer_email'],
                'amount' => $amount,
                'notifyUrl' => $data['callback_url'],
                'returnUrl' => $data['return_url'],
                'cancelUrl' => $data['return_url'],
                'referenceId' => $referenceId,
                'buyerName' => $data['customer_name'],
                'buyerPhone' => $data['customer_phone'] ?? '08123456789',
                'buyerEmail' => $data['customer_email'],
                'paymentMethod' => $this->mapPaymentMethod($data['method'] ?? 'va'),
                'paymentChannel' => $this->mapPaymentChannel($data['method'] ?? 'va'),
                'product' => [
                    $data['addon_name']
                ],
                'qty' => [1],
                'price' => [$amount],
                'weight' => [1],
                'width' => [1],
                'height' => [1],
                'length' => [1],
                'deliveryArea' => '76111',
                'deliveryAddress' => 'Jl. Payment Gateway'
            ];

            $signature = $this->generateSignature($bodyParams);

            Log::info('iPaymu Addon Payment Request', [
                'body_params' => $bodyParams,
                'reference_id' => $referenceId
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => now()->timestamp
            ])->post($this->baseUrl . 'payment/direct', $bodyParams);

            Log::info('iPaymu Addon Payment Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if ($responseData['Status'] == 200) {
                    return [
                        'success' => true,
                        'reference_id' => $referenceId,
                        'session_id' => $responseData['Data']['SessionID'] ?? null,
                        'transaction_id' => $responseData['Data']['TransactionId'] ?? null,
                        'payment_url' => $responseData['Data']['Url'] ?? null,
                        'payment_no' => $responseData['Data']['PaymentNo'] ?? null,
                        'payment_name' => $responseData['Data']['PaymentName'] ?? null,
                        'payment_channel' => $responseData['Data']['PaymentChannel'] ?? null,
                        'va_number' => $responseData['Data']['Via'] ?? null,
                        'qr_string' => $responseData['Data']['QrString'] ?? null,
                        'expired' => $responseData['Data']['Expired'] ?? null,
                        'message' => $responseData['Message'] ?? 'Payment created successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $responseData['Message'] ?? 'Failed to create payment'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to iPaymu: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('iPaymu Addon Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus($transactionId)
    {
        try {
            $bodyParams = [
                'transactionId' => $transactionId
            ];

            $signature = $this->generateSignature($bodyParams);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => now()->timestamp
            ])->post($this->baseUrl . 'transaction', $bodyParams);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('iPaymu check transaction error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Map payment method to iPaymu format
     */
    private function mapPaymentMethod($method)
    {
        $methodMap = [
            'qris' => 'qris',
            'QRIS' => 'qris',
            'va' => 'va',
            'VA' => 'va',
            'BRIVA' => 'va',
            'BNIVA' => 'va',
            'BRIVA' => 'va',
            'MANDIRIVA' => 'va',
            'cstore' => 'cstore',
            'cod' => 'cod',
            'banktransfer' => 'va'
        ];

        return $methodMap[$method] ?? 'va';
    }

    /**
     * Map payment channel to iPaymu format
     */
    private function mapPaymentChannel($method)
    {
        $channelMap = [
            'qris' => 'linkaja',
            'QRIS' => 'linkaja',
            'BRIVA' => 'bri',
            'BNIVA' => 'bni',
            'BCAVA' => 'bca',
            'MANDIRIVA' => 'mandiri',
            'CIMBVA' => 'cimb',
            'PERMATAVA' => 'permata',
            'MYBVA' => 'maybank',
            'va' => 'bag',
            'VA' => 'bag'
        ];

        return $channelMap[$method] ?? 'bag';
    }

    /**
     * Verify callback signature
     */
    public function verifyCallbackSignature($data, $receivedSignature)
    {
        $jsonBody = json_encode($data, JSON_UNESCAPED_SLASHES);
        $bodyHash = strtolower(hash('sha256', $jsonBody));
        $stringToSign = 'POST:' . $this->va . ':' . $bodyHash . ':' . $this->apiKey;
        $calculatedSignature = hash_hmac('sha256', $stringToSign, $this->apiKey);
        
        return hash_equals($calculatedSignature, $receivedSignature);
    }
}

