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
        Schema::create('expense_pos', function (Blueprint $table) {
            $table->id();
            $table->string('pos_name', 100)->comment('Nama pos pengeluaran');
            $table->string('pos_code', 20)->unique()->comment('Kode pos pengeluaran');
            $table->enum('pos_type', ['operasional', 'administrasi', 'akademik', 'fasilitas', 'lainnya'])->default('operasional')->comment('Tipe pos pengeluaran');
            $table->text('pos_description')->nullable()->comment('Keterangan pos pengeluaran');
            $table->boolean('is_active')->default(true)->comment('Status aktif pos pengeluaran');
            $table->timestamps();
            
            // Indexes
            $table->index('pos_name');
            $table->index('pos_code');
            $table->index('pos_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_pos');
    }
};
