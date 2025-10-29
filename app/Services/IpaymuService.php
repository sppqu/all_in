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
        // Ambil kredensial dari database (setup_gateways table)
        $gateway = \DB::table('setup_gateways')->first();
        
        if ($gateway && $gateway->ipaymu_is_active) {
            $this->va = $gateway->ipaymu_va ?? '';
            $this->apiKey = $gateway->ipaymu_api_key ?? '';
            $this->isSandbox = ($gateway->ipaymu_mode ?? 'sandbox') === 'sandbox';
        } else {
            // Fallback to config if database not available or not active
            $this->va = config('ipaymu.va', '');
            $this->apiKey = config('ipaymu.api_key', '');
            $this->isSandbox = config('ipaymu.sandbox', true);
        }
        
        // Set base URL based on mode
        $this->baseUrl = $this->isSandbox 
            ? 'https://sandbox.ipaymu.com/api/v2/'
            : 'https://my.ipaymu.com/api/v2/';
        
        Log::info('iPaymu Service initialized', [
            'source' => $gateway && $gateway->ipaymu_is_active ? 'database' : 'config',
            'va_set' => !empty($this->va) ? 'YES' : 'NO',
            'api_key_set' => !empty($this->apiKey) ? 'YES' : 'NO',
            'is_sandbox' => $this->isSandbox ? 'YES' : 'NO',
            'is_active' => $gateway && $gateway->ipaymu_is_active ? 'YES' : 'NO',
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
                
                // Log detailed response data for debugging
                Log::info('iPaymu Response Data Structure', [
                    'full_response' => $responseData,
                    'data_keys' => isset($responseData['Data']) ? array_keys($responseData['Data']) : 'NO DATA KEY',
                    'via_field' => $responseData['Data']['Via'] ?? 'NOT FOUND',
                    'payment_no' => $responseData['Data']['PaymentNo'] ?? 'NOT FOUND',
                    'payment_code' => $responseData['Data']['PaymentCode'] ?? 'NOT FOUND',
                    'account_number' => $responseData['Data']['AccountNumber'] ?? 'NOT FOUND'
                ]);
                
                // Check if response is success
                if ($responseData['Status'] == 200) {
                    $result = [
                        'success' => true,
                        'reference_id' => $referenceId,
                        'session_id' => $responseData['Data']['SessionID'] ?? null,
                        'transaction_id' => $responseData['Data']['TransactionId'] ?? null,
                        'payment_url' => $responseData['Data']['Url'] ?? null,
                        'payment_no' => $responseData['Data']['PaymentNo'] ?? null,
                        'payment_name' => $responseData['Data']['PaymentName'] ?? null,
                        'payment_channel' => $responseData['Data']['PaymentChannel'] ?? null,
                        'va_number' => $responseData['Data']['Via'] ?? $responseData['Data']['PaymentNo'] ?? $responseData['Data']['PaymentCode'] ?? $responseData['Data']['AccountNumber'] ?? null,
                        'qr_string' => $responseData['Data']['QrString'] ?? null,
                        'expired' => $responseData['Data']['Expired'] ?? null,
                        'message' => $responseData['Message'] ?? 'Payment created successfully'
                    ];
                    
                    Log::info('iPaymu Final Result', [
                        'result' => $result
                    ]);
                    
                    return $result;
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
                
                // Log detailed response data for debugging
                Log::info('iPaymu Addon Response Data Structure', [
                    'full_response' => $responseData,
                    'data_keys' => isset($responseData['Data']) ? array_keys($responseData['Data']) : 'NO DATA KEY',
                    'via_field' => $responseData['Data']['Via'] ?? 'NOT FOUND',
                    'payment_no' => $responseData['Data']['PaymentNo'] ?? 'NOT FOUND',
                    'payment_code' => $responseData['Data']['PaymentCode'] ?? 'NOT FOUND',
                    'account_number' => $responseData['Data']['AccountNumber'] ?? 'NOT FOUND'
                ]);
                
                if ($responseData['Status'] == 200) {
                    $result = [
                        'success' => true,
                        'reference_id' => $referenceId,
                        'session_id' => $responseData['Data']['SessionID'] ?? null,
                        'transaction_id' => $responseData['Data']['TransactionId'] ?? null,
                        'payment_url' => $responseData['Data']['Url'] ?? null,
                        'payment_no' => $responseData['Data']['PaymentNo'] ?? null,
                        'payment_name' => $responseData['Data']['PaymentName'] ?? null,
                        'payment_channel' => $responseData['Data']['PaymentChannel'] ?? null,
                        'va_number' => $responseData['Data']['Via'] ?? $responseData['Data']['PaymentNo'] ?? $responseData['Data']['PaymentCode'] ?? $responseData['Data']['AccountNumber'] ?? null,
                        'qr_string' => $responseData['Data']['QrString'] ?? null,
                        'expired' => $responseData['Data']['Expired'] ?? null,
                        'message' => $responseData['Message'] ?? 'Payment created successfully'
                    ];
                    
                    Log::info('iPaymu Addon Final Result', [
                        'result' => $result
                    ]);
                    
                    return $result;
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
     * Create SPMB payment transaction (Step 2 Registration Fee)
     */
    public function createSPMBPayment($data)
    {
        try {
            $referenceId = 'SPMB-STEP2-' . $data['registration_id'] . '-' . time();
            $amount = (int) $data['amount'];
            
            // Ensure phone number starts with 08 or 62
            $phone = $data['customer_phone'] ?? '08123456789';
            if (!str_starts_with($phone, '08') && !str_starts_with($phone, '62')) {
                $phone = '08' . ltrim($phone, '0');
            }
            
            // Ensure email is valid
            $email = $data['customer_email'] ?? 'spmb@sppqu.com';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = 'spmb@sppqu.com';
            }
            
            $bodyParams = [
                'name' => substr($data['customer_name'], 0, 50), // Max 50 char
                'phone' => $phone,
                'email' => $email,
                'amount' => $amount,
                'notifyUrl' => $data['callback_url'],
                'returnUrl' => $data['return_url'],
                'cancelUrl' => $data['return_url'],
                'referenceId' => $referenceId,
                'buyerName' => substr($data['customer_name'], 0, 50),
                'buyerPhone' => $phone,
                'buyerEmail' => $email,
                'paymentMethod' => $this->mapPaymentMethod($data['method'] ?? 'qris'),
                'paymentChannel' => $this->mapPaymentChannel($data['method'] ?? 'qris'),
                'product' => [
                    'SPMB Registration Fee Step 2'
                ],
                'qty' => [1],
                'price' => [$amount],
                'weight' => [1],
                'width' => [1],
                'height' => [1],
                'length' => [1],
                'deliveryArea' => '76111',
                'deliveryAddress' => 'Jl SPMB Registration'
            ];

            $signature = $this->generateSignature($bodyParams);
            $timestamp = time();

            Log::info('iPaymu SPMB Payment Request', [
                'body_params' => $bodyParams,
                'reference_id' => $referenceId,
                'signature' => $signature,
                'timestamp' => $timestamp,
                'va' => $this->va
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => $timestamp
            ])->post($this->baseUrl . 'payment/direct', $bodyParams);

            Log::info('iPaymu SPMB Payment Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Log detailed response data for debugging
                Log::info('iPaymu SPMB Response Data Structure', [
                    'full_response' => $responseData,
                    'data_keys' => isset($responseData['Data']) ? array_keys($responseData['Data']) : 'NO DATA KEY',
                    'via_field' => $responseData['Data']['Via'] ?? 'NOT FOUND',
                    'payment_no' => $responseData['Data']['PaymentNo'] ?? 'NOT FOUND',
                    'qr_string' => $responseData['Data']['QrString'] ?? 'NOT FOUND'
                ]);
                
                if ($responseData['Status'] == 200) {
                    $result = [
                        'success' => true,
                        'reference_id' => $referenceId,
                        'session_id' => $responseData['Data']['SessionID'] ?? null,
                        'transaction_id' => $responseData['Data']['TransactionId'] ?? null,
                        'payment_url' => $responseData['Data']['Url'] ?? null,
                        'payment_no' => $responseData['Data']['PaymentNo'] ?? null,
                        'payment_name' => $responseData['Data']['PaymentName'] ?? null,
                        'payment_channel' => $responseData['Data']['PaymentChannel'] ?? null,
                        'va_number' => $responseData['Data']['Via'] ?? $responseData['Data']['PaymentNo'] ?? null,
                        'qr_string' => $responseData['Data']['QrString'] ?? null,
                        'qr_code' => $responseData['Data']['QrString'] ?? null,
                        'expired' => $responseData['Data']['Expired'] ?? null,
                        'message' => $responseData['Message'] ?? 'Payment created successfully'
                    ];
                    
                    Log::info('iPaymu SPMB Final Result', [
                        'result' => $result
                    ]);
                    
                    return $result;
                } else {
                    Log::error('iPaymu SPMB Payment Failed (Non-200 Status)', [
                        'status' => $responseData['Status'] ?? 'unknown',
                        'message' => $responseData['Message'] ?? 'No message',
                        'response' => $responseData
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $responseData['Message'] ?? 'Failed to create payment'
                    ];
                }
            }
            
            // Handle HTTP error responses (400, 500, etc)
            $errorBody = $response->json();
            Log::error('iPaymu SPMB API Error', [
                'http_status' => $response->status(),
                'response_body' => $errorBody,
                'request_body' => $bodyParams
            ]);

            return [
                'success' => false,
                'message' => 'iPaymu API Error (' . $response->status() . '): ' . ($errorBody['Message'] ?? $response->body())
            ];

        } catch (\Exception $e) {
            Log::error('iPaymu SPMB Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
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

