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
            // Update payment_method enum to include manual
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'e_wallet', 'manual'])->change();
            
            // Add fields for manual payment
            $table->string('manual_proof_file')->nullable(); // Path to uploaded proof file
            $table->text('manual_notes')->nullable(); // Additional notes for manual payment
            $table->string('manual_bank_name')->nullable(); // Bank name for manual transfer
            $table->string('manual_account_number')->nullable(); // Account number for manual transfer
            $table->string('manual_account_name')->nullable(); // Account name for manual transfer
            $table->decimal('manual_transfer_amount', 10, 2)->nullable(); // Transfer amount
            
            // Add fields for payment gateway
            $table->string('gateway_name')->nullable(); // Payment gateway name (midtrans, xendit, etc.)
            $table->string('gateway_order_id')->nullable(); // Order ID from payment gateway
            $table->text('gateway_response')->nullable(); // Full response from payment gateway
            $table->timestamp('gateway_expired_at')->nullable(); // Expiration time from gateway
            
            // Add verification fields
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable(); // Notes from admin verification
            $table->unsignedBigInteger('verified_by')->nullable(); // Admin who verified
            $table->timestamp('verified_at')->nullable(); // When payment was verified
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_payments', function (Blueprint $table) {
            $table->dropColumn([
                'manual_proof_file',
                'manual_notes',
                'manual_bank_name',
                'manual_account_number',
                'manual_account_name',
                'manual_transfer_amount',
                'gateway_name',
                'gateway_order_id',
                'gateway_response',
                'gateway_expired_at',
                'verification_status',
                'verification_notes',
                'verified_by',
                'verified_at'
            ]);
            
            // Revert payment_method enum
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'e_wallet'])->change();
        });
    }
};
