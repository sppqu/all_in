<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tripay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Tripay payment gateway integration
    |
    */

    'api_key' => env('TRIPAY_API_KEY', 'your_tripay_api_key_here'),
    'private_key' => env('TRIPAY_PRIVATE_KEY', 'your_tripay_private_key_here'),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', 'your_tripay_merchant_code_here'),
    'base_url' => env('TRIPAY_BASE_URL', 'https://tripay.co.id/api/'),
    'is_sandbox' => env('TRIPAY_SANDBOX', true),
    
    /*
    |--------------------------------------------------------------------------
    | SPMB Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SPMB payment amounts
    |
    */
    
    'spmb' => [
        'registration_fee' => 50000,
        'spmb_fee' => 200000,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods for SPMB
    |
    */
    
    'payment_methods' => [
        'QRIS' => 'QRIS',
        'BRIVA' => 'BRI Virtual Account',
        'MANDIRI' => 'Mandiri Virtual Account',
        'BNI' => 'BNI Virtual Account',
        'BCA' => 'BCA Virtual Account',
        'OVO' => 'OVO',
        'DANA' => 'DANA',
        'SHOPEEPAY' => 'ShopeePay',
        'GOPAY' => 'GoPay',
        'LINKAJA' => 'LinkAja',
    ],
];
