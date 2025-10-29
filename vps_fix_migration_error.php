<?php

/**
 * Fix MySQL Strict Mode Migration Error
 * Run: php vps_fix_migration_error.php
 * 
 * Fixes: Invalid default value for 'due_date' error
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Fix Migration Error - Invalid Default Value\n";
echo "===============================================\n\n";

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "🔍 Checking subscription_invoices table...\n";
    
    // Check if table exists
    if (!Schema::hasTable('subscription_invoices')) {
        echo "ℹ️  Table 'subscription_invoices' doesn't exist yet. Migration will create it.\n";
        echo "✅ No fix needed. Run migration normally.\n";
        exit(0);
    }
    
    echo "✅ Table exists\n\n";
    
    // Check current columns
    $columns = DB::select("SHOW COLUMNS FROM subscription_invoices");
    
    echo "📋 Current columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col->Field} ({$col->Type})\n";
        
        // Check if due_date has invalid default
        if ($col->Field === 'due_date' && strpos($col->Default, '0000-00-00') !== false) {
            echo "    ⚠️  Invalid default value detected!\n";
        }
    }
    echo "\n";
    
    // Fix due_date column if it has invalid default
    echo "🔧 Fixing due_date column...\n";
    
    DB::statement("ALTER TABLE subscription_invoices 
        MODIFY COLUMN due_date TIMESTAMP NULL DEFAULT NULL");
    
    echo "✅ due_date column fixed!\n\n";
    
    // Check if payment_reference already exists
    $hasPaymentRef = collect($columns)->contains(function($col) {
        return $col->Field === 'payment_reference';
    });
    
    if ($hasPaymentRef) {
        echo "ℹ️  Column 'payment_reference' already exists. Migration will skip.\n";
    } else {
        echo "ℹ️  Column 'payment_reference' will be added by migration.\n";
    }
    
    echo "\n✅ Fix completed! Now run migration:\n";
    echo "   php artisan migrate --force\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        echo "💡 Table doesn't exist yet. This is normal for first-time setup.\n";
        echo "   Just run: php artisan migrate --force\n\n";
    }
    
    exit(1);
}

