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
        Schema::create('bebas', function (Blueprint $table) {
            $table->id('bebas_id');
            $table->unsignedBigInteger('student_student_id');
            $table->unsignedBigInteger('payment_payment_id');
            $table->decimal('bebas_bill', 10, 0);
            $table->decimal('bebas_total_pay', 10, 0)->default(0);
            $table->text('bebas_desc')->nullable();
            $table->timestamp('bebas_input_date')->nullable()->useCurrent();
            $table->timestamp('bebas_last_update')->nullable()->useCurrent();
            
            // Foreign key constraints
            $table->foreign('student_student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('payment_payment_id')->references('payment_id')->on('payment')->onDelete('cascade');
            
            // Indexes
            $table->index('student_student_id');
            $table->index('payment_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bebas');
    }
};
