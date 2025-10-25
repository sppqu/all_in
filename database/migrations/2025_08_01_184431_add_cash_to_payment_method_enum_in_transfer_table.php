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
        // Modify the payment_method column to include 'cash'
        DB::statement("ALTER TABLE transfer MODIFY COLUMN payment_method ENUM('bank_transfer','credit_card','e_wallet','midtrans','cash')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'cash' from the ENUM
        DB::statement("ALTER TABLE transfer MODIFY COLUMN payment_method ENUM('bank_transfer','credit_card','e_wallet','midtrans')");
    }
};
