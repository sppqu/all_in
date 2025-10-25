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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'payment_online', 'payment_cash', etc
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('fa-info-circle');
            $table->string('color')->default('info'); // info, success, warning, danger
            $table->json('data')->nullable(); // Additional data like payment amount, student name, etc
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
