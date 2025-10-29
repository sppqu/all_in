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

    /**
     * Constructor
     * 
     * @param bool $useEnvConfig Force use ENV config instead of database
     *                           TRUE = Use ENV (for addon/subscription - internal system)
     *                           FALSE = Use Database (for student payments)
     */
    public function __construct($useEnvConfig = false)
    {
        $source = 'config'; // default
        
        if ($useEnvConfig) {
            // Try ENV config first for internal system (addon, subscription, SPMB Step 2)
            $this->va = config('ipaymu.va', '');
            $this->apiKey = config('ipaymu.api_key', '');
            $this->isSandbox = config('ipaymu.sandbox', true);
            
            // If ENV is empty, fallback to database
            if (empty($this->va) || empty($this->apiKey)) {
                Log::warning('⚠️ ENV config empty, falling back to database');
                $gateway = \DB::table('setup_gateways')->first();
                
                if ($gateway && $gateway->ipaymu_is_active) {
                    $this->va = $gateway->ipaymu_va ?? '';
                    $this->apiKey = $gateway->ipaymu_api_key ?? '';
                    $this->isSandbox = ($gateway->ipaymu_mode ?? 'sandbox') === 'sandbox';
                    $source = 'env_fallback_to_db';
                } else {
                    $source = 'env_empty';
                }
            } else {
                $source = 'env_config';
            }
        } else {
            // Use database config for student payments (cart, bulanan, bebas, tabungan)
            $gateway = \DB::table('setup_gateways')->first();
            
            if ($gateway && $gateway->ipaymu_is_active) {
                $this->va = $gateway->ipaymu_va ?? '';
                $this->apiKey = $gateway->ipaymu_api_key ?? '';
                $this->isSandbox = ($gateway->ipaymu_mode ?? 'sandbox') === 'sandbox';
                $source = 'database';
            } else {
                // Fallback to config if database not available or not active
                $this->va = config('ipaymu.va', '');
                $this->apiKey = config('ipaymu.api_key', '');
                $this->isSandbox = config('ipaymu.sandbox', true);
                $source = 'db_fallback_to_env';
            }
        }
        
        // Set base URL based on mode
        $this->baseUrl = $this->isSandbox 
            ? 'https://sandbox.ipaymu.com/api/v2/'
            : 'https://my.ipaymu.com/api/v2/';
        
        // Validate credentials are set
        $credentialsValid = !empty($this->va) && !empty($this->apiKey);
        
        Log::info('iPaymu Service initialized', [
            'source' => $source,
            'use_env_config' => $useEnvConfig ? 'YES' : 'NO',
            'va_set' => !empty($this->va) ? 'YES (len:'.strlen($this->va).')' : '❌ NO - EMPTY!',
            'api_key_set' => !empty($this->apiKey) ? 'YES (len:'.strlen($this->apiKey).')' : '❌ NO - EMPTY!',
            'credentials_valid' => $credentialsValid ? '✅ VALID' : '❌ INVALID',
            'is_sandbox' => $this->isSandbox ? 'YES' : 'NO',
            'base_url' => $this->baseUrl
        ]);
        
        // Warn if credentials are empty
        if (!$credentialsValid) {
            Log::warning('⚠️ iPaymu credentials are EMPTY! Payment will fail!', [
                'requested_source' => $useEnvConfig ? 'ENV' : 'DATABASE',
                'actual_source' => $source,
                'va_empty' => empty($this->va),
                'api_key_empty' => empty($this->apiKey)
            ]);
        }
    }

    /**
     * Generate signature for iPaymu POST requests
     * Signature = HMAC_SHA256(VA + ApiKey + RequestBody, ApiKey)
     * 
     * According to iPaymu documentation:
     * - For POST: hash_hmac('sha256', VA + ApiKey + JSON_BODY, ApiKey)
     * - For GET: hash_hmac('sha256', VA + ApiKey, ApiKey)
     */
    private function generateSignature($bodyParams)
    {
        $jsonBody = json_encode($bodyParams, JSON_UNESCAPED_SLASHES);
        
        // Correct signature format for iPaymu POST
        $stringToSign = $this->va . $this->apiKey . $jsonBody;
        $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
        
        Log::info('iPaymu Signature Generated (POST)', [
            'va_length' => strlen($this->va),
            'api_key_length' => strlen($this->apiKey),
            'body_length' => strlen($jsonBody),
            'string_to_sign_length' => strlen($stringToSign),
            'signature' => $signature
        ]);
        
        return $signature;
    }

    /**
     * Generate signature for iPaymu GET requests
     * For GET requests, no body is included
     */
    private function generateSignatureGet()
    {
        // Correct signature format for iPaymu GET
        $stringToSign = $this->va . $this->apiKey;
        $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
        
        Log::info('iPaymu Signature Generated (GET)', [
            'va_length' => strlen($this->va),
            'api_key_length' => strlen($this->apiKey),
            'string_to_sign_length' => strlen($stringToSign),
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

            Log::info('iPaymu SPMB Payment Request', [
                'body_params' => $bodyParams,
                'reference_id' => $referenceId,
                'signature' => $signature,
                'timestamp' => now()->timestamp,
                'va' => $this->va,
                'va_length' => strlen($this->va),
                'api_key_length' => strlen($this->apiKey),
                'base_url' => $this->baseUrl,
                'is_sandbox' => $this->isSandbox
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => now()->timestamp  // Use same format as addon payment
            ])->post($this->baseUrl . 'payment/direct', $bodyParams);

            $responseBody = $response->json();
            
            Log::info('iPaymu SPMB Payment Response', [
                'status' => $response->status(),
                'body' => $responseBody
            ]);

            // Handle 401 Unauthorized specifically
            if ($response->status() === 401) {
                Log::error('❌ iPaymu 401 Unauthorized - Signature Invalid', [
                    'va' => $this->va ? 'SET (len:'.strlen($this->va).')' : 'EMPTY',
                    'api_key' => $this->apiKey ? 'SET (len:'.strlen($this->apiKey).')' : 'EMPTY',
                    'base_url' => $this->baseUrl,
                    'is_sandbox' => $this->isSandbox,
                    'signature_sent' => $signature,
                    'error_response' => $responseBody
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Kredensial iPaymu tidak valid. Silakan hubungi administrator untuk mengecek konfigurasi iPaymu.',
                    'error_code' => 401
                ];
            }

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
     * Create generic payment (for cart, bulanan, bebas, tabungan)
     */
    public function createPayment($referenceId, $description, $totalAmount, $customerName, $customerPhone, $customerEmail, $products, $quantities, $prices, $callbackUrl, $returnUrl)
    {
        try {
            Log::info('💳 Creating iPaymu payment', [
                'reference_id' => $referenceId,
                'amount' => $totalAmount,
                'products_count' => count($products),
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail
            ]);

            $body = [
                'name' => $customerName,
                'phone' => $customerPhone,
                'email' => $customerEmail,
                'amount' => (int) $totalAmount,
                'notifyUrl' => $callbackUrl,
                'expired' => 24, // 24 hours
                'expiredType' => 'hours',
                'comments' => $description,
                'referenceId' => $referenceId,
                // Don't set paymentMethod & paymentChannel to let user choose on iPaymu page
                // This will show all available payment options: VA (all banks), QRIS, E-Wallet, Retail, etc.
                'product' => $products,
                'qty' => $quantities,
                'price' => $prices,
                'returnUrl' => $returnUrl,
                'cancelUrl' => $returnUrl,
                'continueUrl' => $returnUrl
            ];

            Log::info('💳 iPaymu request body', [
                'body' => $body,
                'is_sandbox' => $this->isSandbox,
                'base_url' => $this->baseUrl
            ]);

            $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
            $bodyHash = strtolower(hash('sha256', $jsonBody));
            $stringToSign = 'POST:' . $this->va . ':' . $bodyHash . ':' . $this->apiKey;
            $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);

            $timestamp = now()->timestamp;

            // Use 'payment' endpoint (redirect) instead of 'payment/direct'
            // This shows iPaymu payment page with ALL payment methods available
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => $timestamp
            ])->post($this->baseUrl . 'payment', $body);

            $result = $response->json();

            Log::info('💳 iPaymu payment response', [
                'status' => $response->status(),
                'success' => $result['Status'] ?? null,
                'session_id' => $result['Data']['SessionID'] ?? null,
                'full_response' => $result // Log full response untuk debug
            ]);

            if ($response->successful() && isset($result['Status']) && $result['Status'] == 200) {
                $data = $result['Data'] ?? $result['data'] ?? [];
                
                // iPaymu response fields (case-sensitive)
                $sessionId = $data['SessionId'] ?? $data['SessionID'] ?? $data['session_id'] ?? null;
                $transactionId = $data['TransactionId'] ?? $data['transaction_id'] ?? null;
                $paymentNo = $data['PaymentNo'] ?? $data['payment_no'] ?? null;
                $channel = $data['Channel'] ?? $data['channel'] ?? null;
                $via = $data['Via'] ?? $data['via'] ?? null;
                $expired = $data['Expired'] ?? $data['expired'] ?? null;
                $total = $data['Total'] ?? $data['total'] ?? null;
                $fee = $data['Fee'] ?? $data['fee'] ?? null;
                
                // iPaymu will return 'Url' for payment page with all available methods
                // If paymentMethod was set to 'va' specifically, it returns PaymentNo instead
                $paymentUrl = $data['Url'] ?? $data['url'] ?? null;
                
                // If no URL but has PaymentNo (direct VA), we'll show instruction page
                // If has URL, user will choose payment method on iPaymu page
                $isVaPayment = !empty($paymentNo) && !$paymentUrl;
                
                Log::info('💳 iPaymu payment parsed', [
                    'is_va_payment' => $isVaPayment,
                    'payment_no' => $paymentNo,
                    'channel' => $channel,
                    'via' => $via
                ]);
                
                return [
                    'success' => true,
                    'data' => [
                        'payment_url' => $paymentUrl,
                        'payment_no' => $paymentNo,
                        'channel' => $channel,
                        'via' => $via,
                        'session_id' => $sessionId,
                        'transaction_id' => $transactionId,
                        'reference_id' => $referenceId,
                        'expired_time' => $expired,
                        'total' => $total,
                        'fee' => $fee,
                        'is_va_payment' => $isVaPayment
                    ],
                    'message' => 'Payment created successfully'
                ];
            }

            Log::error('💳 iPaymu payment failed', [
                'status' => $response->status(),
                'result_status' => $result['Status'] ?? 'NO_STATUS',
                'message' => $result['Message'] ?? $result['message'] ?? 'No message',
                'response' => $result
            ]);

            return [
                'success' => false,
                'message' => $result['Message'] ?? $result['message'] ?? 'Failed to create payment',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('💳 iPaymu payment exception', [
                'error' => $e->getMessage(),
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

