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
        Schema::create('spmb_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('spmb_registrations')->onDelete('cascade');
            $table->enum('type', ['registration_fee', 'spmb_fee']);
            $table->decimal('amount', 15, 2);
            $table->string('payment_method');
            $table->string('payment_reference')->unique();
            $table->string('tripay_reference')->nullable();
            $table->enum('status', ['pending', 'paid', 'expired', 'failed', 'cancelled'])->default('pending');
            $table->text('payment_url')->nullable();
            $table->text('qr_code')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['registration_id', 'type']);
            $table->index(['payment_reference']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_payments');
    }
};
