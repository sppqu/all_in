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
        Schema::create('expense_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_transaksi', 50)->unique();
            $table->unsignedBigInteger('pos_pengeluaran_id');
            $table->decimal('jumlah_pengeluaran', 15, 2);
            $table->text('keterangan')->nullable();
            $table->string('operator', 100)->nullable();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('confirmed');
            $table->timestamps();
            
            $table->foreign('pos_pengeluaran_id')->references('id')->on('expense_pos')->onDelete('restrict');
            $table->index(['tanggal', 'pos_pengeluaran_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_transactions');
    }
};
