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
        Schema::table('transaksi_pengeluaran', function (Blueprint $table) {
            $table->unsignedBigInteger('kas_id')->nullable()->after('operator');
            $table->foreign('kas_id')->references('id')->on('kas')->onDelete('set null');
            $table->index('kas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_pengeluaran', function (Blueprint $table) {
            $table->dropForeign(['kas_id']);
            $table->dropIndex(['kas_id']);
            $table->dropColumn('kas_id');
        });
    }
};
