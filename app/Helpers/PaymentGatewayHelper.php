<?php

namespace App\Helpers;

use App\Models\SetupGateway;

class PaymentGatewayHelper
{
    /**
     * Get active payment gateway
     */
    public static function getActiveGateway()
    {
        $gateway = SetupGateway::first();
        if (!$gateway) {
            return null;
        }
        
        // Check if Midtrans is active and configured
        if ($gateway->midtrans_is_active && !empty($gateway->midtrans_server_key_sandbox) && !empty($gateway->midtrans_client_key_sandbox)) {
            return 'midtrans';
        }
        
        // Check other gateways
        if ($gateway->payment_gateway === 'tripay' && !empty($gateway->apikey_tripay)) {
            return 'tripay';
        }
        
        if ($gateway->payment_gateway === 'duitku' && !empty($gateway->apikey_duitku)) {
            return 'duitku';
        }
        
        return null;
    }
    
    /**
     * Check if specific gateway is active
     */
    public static function isGatewayActive($gatewayName)
    {
        return self::getActiveGateway() === $gatewayName;
    }
    
    /**
     * Get gateway configuration
     */
    public static function getGatewayConfig($gatewayName = null)
    {
        $gateway = SetupGateway::first();
        if (!$gateway) {
            return null;
        }
        
        $activeGateway = $gatewayName ?: $gateway->payment_gateway;
        
        switch ($activeGateway) {
            case 'tripay':
                return [
                    'url' => $gateway->url_tripay,
                    'api_key' => $gateway->apikey_tripay,
                    'private_key' => $gateway->privatekey_tripay,
                    'merchant_code' => $gateway->merchantcode_tripay,
                ];
                
            case 'duitku':
                return [
                    'url' => $gateway->url_duitku,
                    'api_key' => $gateway->apikey_duitku,
                    'merchant_code' => $gateway->merchantcode_duitku,
                    'sandbox' => $gateway->duitku_sandbox,
                ];
                
            case 'midtrans':
                return MidtransHelper::getConfig();
                
            default:
                return null;
        }
    }
    
    /**
     * Check if any gateway is configured
     */
    public static function hasActiveGateway()
    {
        $activeGateway = self::getActiveGateway();
        if (!$activeGateway) {
            return false;
        }
        
        $config = self::getGatewayConfig($activeGateway);
        if (!$config) {
            return false;
        }
        
        // Check if required fields are filled
        switch ($activeGateway) {
            case 'tripay':
                return !empty($config['url']) && !empty($config['api_key']) && !empty($config['private_key']);
                
            case 'duitku':
                return !empty($config['url']) && !empty($config['api_key']) && !empty($config['merchant_code']);
                
            case 'midtrans':
                // Debug: Log the config values
                \Log::info('Midtrans Config Check:', [
                    'server_key' => !empty($config['server_key']),
                    'client_key' => !empty($config['client_key']),
                    'config' => $config
                ]);
                return !empty($config['server_key']) && !empty($config['client_key']);
                
            default:
                return false;
        }
    }
    
    /**
     * Get gateway display name
     */
    public static function getGatewayDisplayName($gatewayName)
    {
        $names = [
            'tripay' => 'Tripay',
            'duitku' => 'Duitku',
            'midtrans' => 'Midtrans',
        ];
        
        return $names[$gatewayName] ?? $gatewayName;
    }
    
    /**
     * Get all available gateways
     */
    public static function getAvailableGateways()
    {
        return [
            'tripay' => 'Tripay',
            'duitku' => 'Duitku',
            'midtrans' => 'Midtrans',
        ];
    }
    
    /**
     * Update active gateway
     */
    public static function updateActiveGateway($gatewayName)
    {
        $gateway = SetupGateway::first() ?? new SetupGateway();
        $gateway->payment_gateway = $gatewayName;
        return $gateway->save();
    }
    
    /**
     * Check if specific gateway is active and configured
     */
    public static function isGatewayActiveAndConfigured($gatewayName)
    {
        $gateway = SetupGateway::first();
        if (!$gateway) {
            return false;
        }
        
        switch ($gatewayName) {
            case 'midtrans':
                return $gateway->midtrans_is_active && 
                       !empty($gateway->midtrans_server_key_sandbox) && 
                       !empty($gateway->midtrans_client_key_sandbox);
                
            case 'tripay':
                return $gateway->payment_gateway === 'tripay' && 
                       !empty($gateway->apikey_tripay) && 
                       !empty($gateway->privatekey_tripay);
                
            case 'duitku':
                return $gateway->payment_gateway === 'duitku' && 
                       !empty($gateway->apikey_duitku) && 
                       !empty($gateway->merchantcode_duitku);
                
            default:
                return false;
        }
    }
} 