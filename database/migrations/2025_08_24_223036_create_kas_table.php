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
        Schema::create('kas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kas');
            $table->text('deskripsi')->nullable();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('jenis_kas')->default('bank'); // bank, cash, e-wallet
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
            $table->index('jenis_kas');
            $table->index('nama_kas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
