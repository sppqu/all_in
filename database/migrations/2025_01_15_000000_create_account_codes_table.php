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
        Schema::create('account_codes', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 32)->unique();
            $table->string('nama', 128);
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['aktiva', 'pasiva', 'modal', 'pendapatan', 'beban'])->default('aktiva');
            $table->enum('kategori', ['lancar', 'tetap', 'pendapatan', 'beban_operasional', 'beban_non_operasional'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_codes');
    }
}; 