<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update transfer table - change payment_method ENUM to include ipaymu
        DB::statement("ALTER TABLE transfer MODIFY COLUMN payment_method VARCHAR(50) DEFAULT NULL");
        
        // Update transfer_detail table if exists
        if (Schema::hasColumn('transfer_detail', 'payment_method')) {
            DB::statement("ALTER TABLE transfer_detail MODIFY COLUMN payment_method VARCHAR(50) DEFAULT NULL");
        }
        
        // Update log_trx table if exists
        if (Schema::hasTable('log_trx') && Schema::hasColumn('log_trx', 'payment_method')) {
            DB::statement("ALTER TABLE log_trx MODIFY COLUMN payment_method VARCHAR(50) DEFAULT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (optional)
        // DB::statement("ALTER TABLE transfer MODIFY COLUMN payment_method ENUM('manual', 'transfer', 'gateway', 'tripay', 'midtrans') DEFAULT NULL");
    }
};

