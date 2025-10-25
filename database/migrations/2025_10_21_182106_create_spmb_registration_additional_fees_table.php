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
        Schema::create('spmb_registration_additional_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('spmb_registrations')->onDelete('cascade');
            $table->foreignId('additional_fee_id')->constrained('spmb_additional_fees')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0); // Jumlah yang harus dibayar
            $table->boolean('is_paid')->default(false); // Status pembayaran
            $table->timestamp('paid_at')->nullable(); // Tanggal pembayaran
            $table->json('metadata')->nullable(); // Data tambahan (ukuran seragam, dll)
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['registration_id', 'additional_fee_id'], 'spmb_reg_add_fees_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_registration_additional_fees');
    }
};
