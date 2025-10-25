<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fonnte WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi dengan Fonnte WhatsApp API
    |
    */

    'api_key' => env('FONNTE_API_KEY'),
    
    'base_url' => env('FONNTE_URL', 'https://api.fonnte.com/send'),
    
    'country_code' => env('FONNTE_COUNTRY_CODE', '62'),
    
    'timeout' => env('FONNTE_TIMEOUT', 30),
    
    'retry_attempts' => env('FONNTE_RETRY_ATTEMPTS', 3),
    
    'otp_expiry_minutes' => env('OTP_EXPIRY_MINUTES', 5),
    
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 3),
];
