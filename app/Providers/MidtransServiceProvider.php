<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config;
use Midtrans\Snap;
use App\Helpers\MidtransHelper;
use Illuminate\Support\Facades\Log;

class MidtransServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('midtrans', function ($app) {
            try {
                // Get configuration from database using helper
                $config = MidtransHelper::getConfig();
                
                // Log configuration for debugging
                Log::info('MidtransServiceProvider Config:', [
                    'mode' => $config['mode'] ?? 'unknown',
                    'is_production' => $config['is_production'] ?? false,
                    'server_key_length' => strlen($config['server_key'] ?? ''),
                    'client_key_length' => strlen($config['client_key'] ?? '')
                ]);
                
                // Set konfigurasi Midtrans
                Config::$serverKey = $config['server_key'];
                Config::$clientKey = $config['client_key'];
                Config::$isProduction = $config['is_production'];
                Config::$isSanitized = true;
                Config::$is3ds = true;
                
                return new class {
                    public function createSnapToken($params)
                    {
                        try {
                            return Snap::getSnapToken($params);
                        } catch (\Exception $e) {
                            Log::error('Midtrans SDK createSnapToken error: ' . $e->getMessage());
                            throw $e;
                        }
                    }
                    
                    public function createSnapUrl($params)
                    {
                        try {
                            return Snap::getSnapUrl($params);
                        } catch (\Exception $e) {
                            Log::error('Midtrans SDK createSnapUrl error: ' . $e->getMessage());
                            throw $e;
                        }
                    }
                    
                    public function getStatus($orderId)
                    {
                        try {
                            return \Midtrans\Transaction::status($orderId);
                        } catch (\Exception $e) {
                            Log::error('Midtrans SDK getStatus error: ' . $e->getMessage());
                            throw $e;
                        }
                    }
                };
            } catch (\Exception $e) {
                Log::error('MidtransServiceProvider registration error: ' . $e->getMessage());
                
                // Return a fallback service that logs errors
                return new class {
                    public function createSnapToken($params)
                    {
                        Log::error('Midtrans service not properly configured');
                        throw new \Exception('Midtrans service not properly configured');
                    }
                    
                    public function createSnapUrl($params)
                    {
                        Log::error('Midtrans service not properly configured');
                        throw new \Exception('Midtrans service not properly configured');
                    }
                    
                    public function getStatus($orderId)
                    {
                        Log::error('Midtrans service not properly configured');
                        throw new \Exception('Midtrans service not properly configured');
                    }
                };
            }
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 