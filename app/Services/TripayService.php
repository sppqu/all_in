<?php

namespace App\Services;

use App\Models\SetupGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    protected $apiKey;
    protected $privateKey;
    protected $merchantCode;
    protected $baseUrl;
    protected $isSandbox;

    public function __construct()
    {
        $gateway = SetupGateway::first();
        
        if (!$gateway || $gateway->payment_gateway !== 'tripay') {
            throw new \Exception('Tripay gateway not configured');
        }

        $this->apiKey = $gateway->apikey_tripay;
        $this->privateKey = $gateway->privatekey_tripay;
        $this->merchantCode = $gateway->merchantcode_tripay;
        $this->baseUrl = $gateway->url_tripay ?? 'https://tripay.co.id/api/';
        $this->isSandbox = true; // Set to false for production
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
            Log::error('Tripay get payment channels error', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create transaction
     */
    public function createTransaction($data)
    {
        try {
            // Generate signature first
            $signature = $this->generateSignature($data['merchant_ref'], $data['amount']);
            
            $payload = [
                'method' => $data['method'],
                'merchant_ref' => $data['merchant_ref'],
                'amount' => $data['amount'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? 'student@sppqu.com',
                'customer_phone' => $data['customer_phone'] ?? '08123456789',
                'order_items' => $data['order_items'],
                'return_url' => $data['return_url'],
                'callback_url' => $data['callback_url'],
                'expired_time' => (time() + (24 * 60 * 60)), // 24 hours
                'signature' => $signature
            ];

            Log::info('Tripay transaction payload', [
                'payload' => $payload,
                'api_key_length' => strlen($this->apiKey),
                'base_url' => $this->baseUrl
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . 'transaction/create', $payload);

            Log::info('Tripay response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Tripay successful response', $responseData);
                return $responseData;
            }

            Log::error('Tripay create transaction failed', [
                'payload' => $payload,
                'response' => $response->json(),
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Tripay create transaction error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return null;
        }
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

            Log::error('Tripay get transaction detail failed', [
                'reference' => $reference,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Tripay get transaction detail error', [
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
        // Correct signature format: merchantCode + merchantRef + amount
        $signature = hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey);
        
        Log::info('Tripay signature generation', [
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
        $methods = [
            'BRIVA' => 'BRI Virtual Account',
            'MANDIRI' => 'Mandiri Virtual Account',
            'BNI' => 'BNI Virtual Account',
            'BCA' => 'BCA Virtual Account',
            'OVO' => 'OVO',
            'DANA' => 'DANA',
            'SHOPEEPAY' => 'ShopeePay',
            'GOPAY' => 'GoPay',
            'LINKAJA' => 'LinkAja',
            'QRIS' => 'QRIS'
        ];

        return $methods[$method] ?? $method;
    }
} 