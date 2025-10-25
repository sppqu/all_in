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
        Schema::create('spmb_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('password');
            $table->integer('step')->default(1);
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->boolean('registration_fee_paid')->default(false);
            $table->boolean('spmb_fee_paid')->default(false);
            $table->json('form_data')->nullable();
            $table->timestamps();
            
            $table->index(['phone', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_registrations');
    }
};
