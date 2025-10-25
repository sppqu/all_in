<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('online_payments', function (Blueprint $table) {
            // Update payment_method enum to include midtrans
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'e_wallet', 'midtrans'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_payments', function (Blueprint $table) {
            // Revert payment_method enum
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'e_wallet'])->change();
        });
    }
};
