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
        Schema::table('transfer', function (Blueprint $table) {
            // Add payment method field
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'e_wallet', 'midtrans'])->nullable()->after('detail');
            
            // Add payment gateway specific fields
            $table->string('gateway_transaction_id')->nullable()->after('payment_method');
            $table->text('payment_details')->nullable()->after('gateway_transaction_id'); // JSON data dari payment gateway
            
            // Add bill information
            $table->enum('bill_type', ['bulanan', 'bebas'])->nullable()->after('payment_details');
            $table->unsignedBigInteger('bill_id')->nullable()->after('bill_type');
            
            // Add payment number for consistency
            $table->string('payment_number', 100)->nullable()->after('bill_id');
            
            // Add paid_at timestamp
            $table->timestamp('paid_at')->nullable()->after('payment_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'gateway_transaction_id', 
                'payment_details',
                'bill_type',
                'bill_id',
                'payment_number',
                'paid_at'
            ]);
        });
    }
}; 