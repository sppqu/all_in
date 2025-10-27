<?php

return [
    /*
    |--------------------------------------------------------------------------
    | iPaymu Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for iPaymu Payment Gateway integration
    |
    */

    'va' => env('IPAYMU_VA', ''),
    'api_key' => env('IPAYMU_API_KEY', ''),
    'sandbox' => env('IPAYMU_SANDBOX', true),
    
    'base_url' => env('IPAYMU_SANDBOX', true) 
        ? 'https://sandbox.ipaymu.com/api/v2/'
        : 'https://my.ipaymu.com/api/v2/',
];

