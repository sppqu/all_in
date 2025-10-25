<?php

namespace App\Helpers;

use App\Models\SetupGateway;

class MidtransHelper
{
    /**
     * Get Midtrans configuration from database
     */
    public static function getConfig()
    {
        $gateway = SetupGateway::first();
        
        if (!$gateway) {
            return [
                'server_key' => env('MIDTRANS_SERVER_KEY', ''),
                'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
                'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
                'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
            ];
        }
        
        // Get environment-specific keys based on mode
        $mode = $gateway->midtrans_mode ?? 'sandbox';
        
        if ($mode === 'sandbox') {
            $serverKey = $gateway->midtrans_server_key_sandbox ?: env('MIDTRANS_SERVER_KEY', '');
            $clientKey = $gateway->midtrans_client_key_sandbox ?: env('MIDTRANS_CLIENT_KEY', '');
            $merchantId = $gateway->midtrans_merchant_id_sandbox ?: env('MIDTRANS_MERCHANT_ID', '');
            $isProduction = false;
        } else {
            $serverKey = $gateway->midtrans_server_key_production ?: env('MIDTRANS_SERVER_KEY_PRODUCTION', '');
            $clientKey = $gateway->midtrans_client_key_production ?: env('MIDTRANS_CLIENT_KEY_PRODUCTION', '');
            $merchantId = $gateway->midtrans_merchant_id_production ?: env('MIDTRANS_MERCHANT_ID_PRODUCTION', '');
            $isProduction = true;
        }
        
        $config = [
            'server_key' => $serverKey,
            'client_key' => $clientKey,
            'merchant_id' => $merchantId,
            'is_production' => $isProduction,
            'mode' => $mode
        ];
        
        // Debug: Log the config
        \Log::info('Midtrans Config:', $config);
        
        return $config;
    }
    
    /**
     * Get server key
     */
    public static function getServerKey()
    {
        $config = self::getConfig();
        return $config['server_key'];
    }
    
    /**
     * Get client key
     */
    public static function getClientKey()
    {
        $config = self::getConfig();
        return $config['client_key'];
    }
    
    /**
     * Get merchant ID
     */
    public static function getMerchantId()
    {
        $config = self::getConfig();
        return $config['merchant_id'];
    }
    
    /**
     * Check if production mode
     */
    public static function isProduction()
    {
        $config = self::getConfig();
        return $config['is_production'];
    }
    
    /**
     * Get snap URL based on environment
     */
    public static function getSnapUrl()
    {
        return self::isProduction() 
            ? 'https://app.midtrans.com/snap/v1/transactions' 
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }
    
    /**
     * Get Snap JavaScript URL based on environment
     */
    public static function getSnapJsUrl()
    {
        return self::isProduction() 
            ? 'https://app.midtrans.com/snap/snap.js' 
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
    
    /**
     * Get API URL based on environment
     */
    public static function getApiUrl()
    {
        return self::isProduction() 
            ? 'https://api.midtrans.com/v2' 
            : 'https://api.sandbox.midtrans.com/v2';
    }
    
    /**
     * Check if Midtrans is configured
     */
    public static function isConfigured()
    {
        $config = self::getConfig();
        return !empty($config['server_key']) && !empty($config['client_key']);
    }
    
    /**
     * Update Midtrans configuration
     */
    public static function updateConfig($data)
    {
        $gateway = SetupGateway::first();
        
        if (!$gateway) {
            $gateway = new SetupGateway();
        }
        
        $gateway->midtrans_mode = $data['mode'] ?? 'sandbox';
        $gateway->midtrans_is_active = $data['is_active'] ?? false;
        
        if (($data['mode'] ?? 'sandbox') === 'sandbox') {
            $gateway->midtrans_server_key_sandbox = $data['server_key'] ?? '';
            $gateway->midtrans_client_key_sandbox = $data['client_key'] ?? '';
            $gateway->midtrans_merchant_id_sandbox = $data['merchant_id'] ?? '';
        } else {
            $gateway->midtrans_server_key_production = $data['server_key'] ?? '';
            $gateway->midtrans_client_key_production = $data['client_key'] ?? '';
            $gateway->midtrans_merchant_id_production = $data['merchant_id'] ?? '';
        }
        
        return $gateway->save();
    }
    
    /**
     * Create Snap Token for Midtrans using HTTP client
     */
    public function createSnapToken($data)
    {
        try {
            // Check if Midtrans is configured
            if (!self::isConfigured()) {
                \Log::error('Midtrans not configured');
                return [
                    'success' => false,
                    'message' => 'Midtrans belum dikonfigurasi'
                ];
            }
            
            $serverKey = self::getServerKey();
            $snapUrl = self::getSnapUrl();
            
            // Validate server key
            if (empty($serverKey)) {
                \Log::error('Midtrans server key is empty');
                return [
                    'success' => false,
                    'message' => 'Server key Midtrans tidak ditemukan'
                ];
            }
            
            // Prepare headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':')
            ];
            
            // Log request data for debugging
            \Log::info('Midtrans Snap Token Request:', [
                'url' => $snapUrl,
                'data' => $data,
                'server_key' => substr($serverKey, 0, 10) . '...',
                'headers' => $headers
            ]);
            
            // Make HTTP request to Midtrans
            $response = \Http::withHeaders($headers)
                ->timeout(30)
                ->post($snapUrl, $data);
            
            $responseData = $response->json();
            
            \Log::info('Midtrans Snap Token Response:', [
                'status' => $response->status(),
                'data' => $responseData,
                'headers' => $response->headers()
            ]);
            
            if ($response->successful() && isset($responseData['token'])) {
                return [
                    'success' => true,
                    'data' => [
                        'token' => $responseData['token'],
                        'redirect_url' => $responseData['redirect_url'] ?? null
                    ]
                ];
            } else {
                $errorMessage = $responseData['error_messages'][0] ?? 'Unknown error';
                \Log::error('Midtrans API error:', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'response' => $responseData
                ]);
                return [
                    'success' => false,
                    'message' => 'Gagal membuat Snap Token: ' . $errorMessage
                ];
            }
            
        } catch (\Exception $e) {
            \Log::error('Midtrans Snap Token Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create Snap Token using Midtrans SDK
     */
    public function createSnapTokenWithSDK($data)
    {
        try {
            // Check if Midtrans is configured
            if (!self::isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Midtrans belum dikonfigurasi'
                ];
            }
            
            $config = self::getConfig();
            
            // Set Midtrans configuration
            \Midtrans\Config::$serverKey = $config['server_key'];
            \Midtrans\Config::$clientKey = $config['client_key'];
            \Midtrans\Config::$isProduction = $config['is_production'];
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;
            
            // Log configuration
            \Log::info('Midtrans SDK Config:', [
                'server_key' => substr($config['server_key'], 0, 10) . '...',
                'client_key' => substr($config['client_key'], 0, 10) . '...',
                'is_production' => $config['is_production']
            ]);
            
            // Create snap token
            $snapToken = \Midtrans\Snap::getSnapToken($data);
            
            \Log::info('Midtrans SDK Snap Token created:', [
                'token' => $snapToken
            ]);
            
            return [
                'success' => true,
                'data' => [
                    'token' => $snapToken
                ]
            ];
            
        } catch (\Exception $e) {
            \Log::error('Midtrans SDK Snap Token Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
} 