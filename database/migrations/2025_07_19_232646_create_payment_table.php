<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->id('payment_id');
            $table->enum('payment_type', ['BEBAS', 'BULAN']);
            $table->unsignedBigInteger('period_period_id');
            $table->unsignedBigInteger('pos_pos_id');
            $table->timestamp('payment_input_date')->nullable();
            $table->timestamp('payment_last_update')->nullable();

            $table->foreign('period_period_id')->references('period_id')->on('periods')->onDelete('cascade');
            $table->foreign('pos_pos_id')->references('pos_id')->on('pos_pembayaran')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
}; 