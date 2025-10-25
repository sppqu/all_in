<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production' || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Fix for MySQL 5.7+ compatibility
        Schema::defaultStringLength(191);

        // Log application startup
        Log::info('AppServiceProvider boot', [
            'environment' => config('app.env'),
            'app_url' => config('app.url'),
            'is_production' => config('app.env') === 'production'
        ]);

        // Check database connection with better error handling
        try {
            DB::connection()->getPdo();
            Log::info('Database connection successful');
            
            // Only check tables if database connection is successful
            $this->checkGeneralSettings();
            
        } catch (\Exception $e) {
            Log::error('Database connection failed: ' . $e->getMessage());
            // Don't throw exception to prevent app from crashing
        }
    }
    
    /**
     * Check general settings tables
     */
    private function checkGeneralSettings(): void
    {
        try {
            $profileExists = Schema::hasTable('general_settings')
                ? (DB::table('general_settings')->exists() ? 'YES' : 'NO')
                : 'NO_TABLE';
            $gatewayExists = Schema::hasTable('setup_gateways')
                ? (DB::table('setup_gateways')->exists() ? 'YES' : 'NO')
                : 'NO_TABLE';
            
            $profileData = null;
            $gatewayData = null;
            
            if ($profileExists === 'YES') {
                $profileData = DB::table('general_settings')->first();
            }
            
            if ($gatewayExists === 'YES') {
                $gatewayData = DB::table('setup_gateways')->first();
            }
            
            Log::info('General Setting data', [
                'profile_exists' => $profileExists,
                'gateway_exists' => $gatewayExists,
                'profile_data' => $profileData,
                'gateway_data' => $gatewayData
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking general settings: ' . $e->getMessage());
            // Don't throw exception to prevent app from crashing
        }
    }
}
