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
        Schema::create('spmb_wave_additional_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wave_id')->constrained('spmb_waves')->onDelete('cascade');
            $table->foreignId('additional_fee_id')->constrained('spmb_additional_fees')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0); // Biaya khusus untuk gelombang ini
            $table->boolean('is_active')->default(true); // Status aktif untuk gelombang ini
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['wave_id', 'additional_fee_id'], 'spmb_wave_add_fees_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_wave_additional_fees');
    }
};
