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
        Schema::create('pos_pengeluaran', function (Blueprint $table) {
            $table->id('pos_id');
            $table->string('pos_name', 100);
            $table->string('pos_description', 100)->nullable();
            $table->enum('pos_type', ['operasional', 'administrasi', 'akademik', 'fasilitas', 'lainnya'])->default('operasional');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('pos_name');
            $table->index('pos_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_pengeluaran');
    }
};
