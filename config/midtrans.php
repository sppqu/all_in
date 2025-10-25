<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Midtrans payment gateway.
    | Update these values with your actual Midtrans credentials.
    |
    */

    // Environment: 'sandbox' or 'production'
    'environment' => env('MIDTRANS_ENVIRONMENT', 'sandbox'),

    // Server Key (from Midtrans Dashboard)
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-kPdWjzufT77jNCgM7EQTYIz5'),

    // Client Key (from Midtrans Dashboard)
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-lJRoDoWDFqA6NzlJ'),

    // Merchant ID (from Midtrans Dashboard)
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', 'G409110172'),

    // Is Production Mode
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Is Sanitized
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    // Is 3DS
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    // Snap URL
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false) 
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    // API URL
    'api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',

    // Callback URLs
    'callback_urls' => [
        'finish' => env('APP_URL') . '/subscription/callback',
        'error' => env('APP_URL') . '/subscription/callback',
        'pending' => env('APP_URL') . '/subscription/callback',
        'unfinish' => env('APP_URL') . '/subscription/callback',
    ],

    // Enabled Payment Methods
    'enabled_payments' => [
        'credit_card',
        'bca_va',
        'bni_va', 
        'bri_va',
        'mandiri_clickpay',
        'gopay',
        'indomaret',
        'danamon_online',
        'akulaku',
        'ovo',
        'dana',
        'shopeepay'
    ],

    // Currency
    'currency' => 'IDR',

    // Language
    'language' => 'id',

    // Default timeout
    'timeout' => 60,

    // Default transaction expiry
    'transaction_expiry' => 24, // hours
]; 