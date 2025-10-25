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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('nama_metode', 100);
            $table->unsignedBigInteger('kas_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['ON', 'OFF'])->default('OFF');
            $table->timestamps();
            
            // Indexes
            $table->index('nama_metode');
            $table->index('kas_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
