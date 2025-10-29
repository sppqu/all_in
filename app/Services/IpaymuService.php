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
            Log::info('🚀 STARTING ENV CONFIG LOAD (useEnvConfig=true)', [
                'method' => 'direct .env file reading',
                'env_path' => base_path('.env')
            ]);
            
            // FORCE load from .env file directly (bypass config cache)
            // This ensures ENV variables are loaded even when config is cached
            $envVa = $this->getEnvValue('IPAYMU_VA');
            $envApiKey = $this->getEnvValue('IPAYMU_API_KEY');
            $envSandbox = $this->getEnvValue('IPAYMU_SANDBOX');
            
            Log::info('📊 ENV values retrieved', [
                'IPAYMU_VA' => $envVa ? 'FOUND' : 'NULL',
                'IPAYMU_API_KEY' => $envApiKey ? 'FOUND' : 'NULL',
                'IPAYMU_SANDBOX' => $envSandbox !== null ? $envSandbox : 'NULL'
            ]);
            
            $this->va = $envVa ?? '';
            $this->apiKey = $envApiKey ?? '';
            $this->isSandbox = $envSandbox !== null ? filter_var($envSandbox, FILTER_VALIDATE_BOOLEAN) : true;
            
            Log::info('🔍 FORCE Loading ENV config from .env file', [
                'env_file_exists' => file_exists(base_path('.env')) ? 'YES' : 'NO',
                'env_va_raw' => $envVa ? substr($envVa, 0, 10).'...'.substr($envVa, -5) : 'NULL/EMPTY',
                'env_api_key_raw' => $envApiKey ? substr($envApiKey, 0, 15).'...' : 'NULL/EMPTY',
                'env_sandbox_raw' => $envSandbox !== null ? $envSandbox : 'NULL',
                'va_loaded' => !empty($this->va) ? 'YES (len:'.strlen($this->va).')' : 'NO',
                'api_key_loaded' => !empty($this->apiKey) ? 'YES (len:'.strlen($this->apiKey).')' : 'NO',
                'sandbox_loaded' => $this->isSandbox ? 'true' : 'false'
            ]);
            
            // If ENV is empty, fallback to database
            if (empty($this->va) || empty($this->apiKey)) {
                Log::warning('⚠️ ENV config empty, falling back to database');
                $gateway = \DB::table('setup_gateways')->first();
                
                if ($gateway && $gateway->ipaymu_is_active) {
                    $this->va = $gateway->ipaymu_va ?? '';
                    $this->apiKey = $gateway->ipaymu_api_key ?? '';
                    $this->isSandbox = ($gateway->ipaymu_mode ?? 'sandbox') === 'sandbox';
                    $source = 'env_fallback_to_db';
                    
                    Log::info('✅ Fallback to database successful', [
                        'va_from_db' => !empty($this->va) ? 'SET (len:'.strlen($this->va).')' : 'EMPTY',
                        'api_key_from_db' => !empty($this->apiKey) ? 'SET (len:'.strlen($this->apiKey).')' : 'EMPTY'
                    ]);
                } else {
                    $source = 'env_empty_no_db';
                    Log::error('❌ Both ENV and Database config are empty!');
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
     * Get ENV value directly from .env file (bypass config cache)
     * 
     * This is needed because env() returns NULL when config is cached
     */
    private function getEnvValue($key)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            Log::warning("⚠️ .env file not found at: {$envPath}");
            return null;
        }
        
        $envContent = file_get_contents($envPath);
        
        // Match the key with optional quotes
        // Supports: KEY=value, KEY="value", KEY='value'
        $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';
        
        if (preg_match($pattern, $envContent, $matches)) {
            $value = trim($matches[1]);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            Log::info("✅ Found {$key} in .env file", [
                'length' => strlen($value),
                'preview' => strlen($value) > 20 ? substr($value, 0, 15).'...' : $value
            ]);
            
            return $value;
        }
        
        Log::warning("⚠️ {$key} not found in .env file");
        return null;
    }

    /**
     * Generate signature for iPaymu POST requests
     * 
     * Correct format according to iPaymu documentation:
     * POST: hash_hmac('sha256', 'POST:' + VA + ':' + lowercase(hash('sha256', BODY)) + ':' + ApiKey, ApiKey)
     */
    private function generateSignature($bodyParams)
    {
        $jsonBody = json_encode($bodyParams, JSON_UNESCAPED_SLASHES);
        
        // Hash the body content with SHA256 and convert to lowercase
        $bodyHash = strtolower(hash('sha256', $jsonBody));
        
        // Create signature string: POST:VA:BODYHASH:APIKEY
        $stringToSign = 'POST:' . $this->va . ':' . $bodyHash . ':' . $this->apiKey;
        
        // Generate HMAC signature
        $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
        
        Log::info('iPaymu Signature Generated (Correct Format)', [
            'method' => 'POST',
            'va_length' => strlen($this->va),
            'api_key_length' => strlen($this->apiKey),
            'body_length' => strlen($jsonBody),
            'body_hash' => substr($bodyHash, 0, 20) . '...',
            'string_to_sign_length' => strlen($stringToSign),
            'signature' => substr($signature, 0, 20) . '...'
        ]);
        
        return $signature;
    }

    /**
     * Generate signature for iPaymu GET requests
     * 
     * Correct format for GET according to iPaymu documentation:
     * GET: hash_hmac('sha256', 'GET:' + VA + ':' + lowercase(hash('sha256', BODY)) + ':' + ApiKey, ApiKey)
     * For GET with no body, BODY = empty string
     */
    private function generateSignatureGet()
    {
        // For GET requests, body is empty
        $bodyHash = strtolower(hash('sha256', ''));
        
        // Create signature string: GET:VA:BODYHASH:APIKEY
        $stringToSign = 'GET:' . $this->va . ':' . $bodyHash . ':' . $this->apiKey;
        
        // Generate HMAC signature
        $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
        
        Log::info('iPaymu Signature Generated (GET - Correct Format)', [
            'method' => 'GET',
            'va_length' => strlen($this->va),
            'api_key_length' => strlen($this->apiKey),
            'body_hash' => $bodyHash,
            'string_to_sign_length' => strlen($stringToSign),
            'signature' => substr($signature, 0, 20) . '...'
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
                'product' => [substr($data['plan_name'], 0, 100)],
                'qty' => [1],
                'price' => [$amount],
                'description' => ['Subscription - ' . substr($data['plan_name'], 0, 80)],
                'weight' => [1],
                'width' => [1],
                'height' => [1],
                'length' => [1],
                'deliveryArea' => '76111',
                'deliveryAddress' => 'Indonesia'
            ];
            
            // Remove null values for production
            if (!$this->isSandbox) {
                $bodyParams = array_filter($bodyParams, function($value) {
                    return $value !== null && $value !== '';
                });
            }

            $signature = $this->generateSignature($bodyParams);

            Log::info('iPaymu Subscription Payment Request', [
                'body_params' => $bodyParams,
                'reference_id' => $referenceId
            ]);

            $timestamp = now()->timestamp;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => (string) $timestamp
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
            
            // Validate phone for production
            $phone = $data['customer_phone'] ?? '08123456789';
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            if (!$this->isSandbox && ($phone === '08123456789' || strlen($phone) < 10)) {
                Log::error('❌ PRODUCTION ERROR: Cannot use dummy phone number', [
                    'phone' => $phone,
                    'user_id' => $data['user_id'] ?? null,
                    'message' => 'Please update user phone number in database or switch to sandbox mode'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Tidak dapat melakukan pembayaran. Nomor HP tidak valid untuk mode production. Silakan update nomor HP di profil atau hubungi administrator.'
                ];
            }
            
            // Production mode requires specific address
            $deliveryAddress = $this->isSandbox 
                ? 'Indonesia' 
                : 'Jl. Pembelian Addon SPPQU, Jakarta, Indonesia';
            
            $bodyParams = [
                'name' => $data['customer_name'],
                'phone' => $phone,
                'email' => $data['customer_email'],
                'amount' => $amount,
                'notifyUrl' => $data['callback_url'],
                'returnUrl' => $data['return_url'],
                'cancelUrl' => $data['return_url'],
                'referenceId' => $referenceId,
                'buyerName' => $data['customer_name'],
                'buyerPhone' => $phone,
                'buyerEmail' => $data['customer_email'],
                'paymentMethod' => $this->mapPaymentMethod($data['method'] ?? 'va'),
                'paymentChannel' => $this->mapPaymentChannel($data['method'] ?? 'va'),
                'product' => [substr($data['addon_name'], 0, 100)],
                'qty' => [1],
                'price' => [$amount],
                'description' => ['Pembelian ' . substr($data['addon_name'], 0, 80)],
                'weight' => [1],
                'width' => [1],
                'height' => [1],
                'length' => [1],
                'deliveryArea' => $this->isSandbox ? '76111' : '10110',
                'deliveryAddress' => $deliveryAddress
            ];
            
            // Remove null values for production
            if (!$this->isSandbox) {
                $bodyParams = array_filter($bodyParams, function($value) {
                    return $value !== null && $value !== '';
                });
            }

            $signature = $this->generateSignature($bodyParams);

            Log::info('iPaymu Addon Payment Request', [
                'body_params' => $bodyParams,
                'reference_id' => $referenceId
            ]);

            $timestamp = now()->timestamp;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => (string) $timestamp
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
            
            // Validate phone number (production requires real phone)
            $phone = $data['customer_phone'] ?? '08123456789';
            
            // Clean phone number
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            if (!str_starts_with($phone, '08') && !str_starts_with($phone, '62')) {
                $phone = '08' . ltrim($phone, '0');
            }
            
            // For production, don't use dummy numbers
            if (!$this->isSandbox && ($phone === '08123456789' || strlen($phone) < 10)) {
                Log::error('❌ PRODUCTION ERROR: Cannot use dummy phone number for SPMB', [
                    'phone' => $phone,
                    'registration_id' => $data['registration_id'] ?? null,
                    'message' => 'Please provide real customer phone or switch to sandbox mode'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Nomor HP tidak valid untuk mode production. Silakan gunakan nomor HP yang valid atau hubungi administrator.'
                ];
            }
            
            // Validate email (production requires valid email)
            $email = $data['customer_email'] ?? 'customer@sppqu.com';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = 'customer@sppqu.com';
            }
            
            // For production, avoid generic emails
            if (!$this->isSandbox && (strpos($email, 'test@') === 0 || strpos($email, 'admin@') === 0)) {
                Log::warning('⚠️ Using generic email in production mode');
            }
            
            // Production mode requires real/specific address
            $deliveryAddress = $this->isSandbox 
                ? 'Indonesia' 
                : ($data['delivery_address'] ?? 'Jl. Pendaftaran SPMB No. 1, Jakarta, Indonesia');
            
            $deliveryArea = $this->isSandbox 
                ? '76111' 
                : ($data['delivery_area'] ?? '10110');
            
            // Production mode requires stricter data validation
            $bodyParams = [
                'name' => substr($data['customer_name'], 0, 50),
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
                'product' => ['SPMB Registration Fee'],
                'qty' => [1],
                'price' => [$amount],
                'description' => ['Biaya Pendaftaran SPMB'],
                'weight' => [1],
                'width' => [1],
                'height' => [1],
                'length' => [1],
                'deliveryArea' => $deliveryArea,
                'deliveryAddress' => $deliveryAddress
            ];
            
            // Remove null values for production (stricter validation)
            if (!$this->isSandbox) {
                $bodyParams = array_filter($bodyParams, function($value) {
                    return $value !== null && $value !== '';
                });
            }

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

            $timestamp = now()->timestamp;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'signature' => $signature,
                'va' => $this->va,
                'timestamp' => (string) $timestamp  // Ensure string format
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

