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
        // Update transfer table - change bill_type to VARCHAR to support cart, tabungan, etc
        DB::statement("ALTER TABLE transfer MODIFY COLUMN bill_type VARCHAR(50) DEFAULT NULL");
        
        // Update transfer_detail table if exists
        if (Schema::hasColumn('transfer_detail', 'bill_type')) {
            DB::statement("ALTER TABLE transfer_detail MODIFY COLUMN bill_type VARCHAR(50) DEFAULT NULL");
        }
        
        // Update log_trx table if exists
        if (Schema::hasTable('log_trx') && Schema::hasColumn('log_trx', 'bill_type')) {
            DB::statement("ALTER TABLE log_trx MODIFY COLUMN bill_type VARCHAR(50) DEFAULT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (optional)
        // DB::statement("ALTER TABLE transfer MODIFY COLUMN bill_type ENUM('bulanan', 'bebas') DEFAULT NULL");
    }
};

