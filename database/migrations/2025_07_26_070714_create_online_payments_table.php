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
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 100)->unique();
            $table->unsignedBigInteger('student_id');
            $table->enum('bill_type', ['bulanan', 'bebas']);
            $table->unsignedBigInteger('bill_id');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'e_wallet']);
            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            $table->text('payment_details')->nullable(); // JSON data dari payment gateway
            $table->string('gateway_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_payments');
    }
};
