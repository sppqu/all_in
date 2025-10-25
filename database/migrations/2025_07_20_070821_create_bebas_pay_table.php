<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bebas_pay', function (Blueprint $table) {
            $table->id('bebas_pay_id');
            $table->unsignedBigInteger('bebas_bebas_id');
            $table->string('bebas_pay_number', 100)->nullable();
            $table->decimal('bebas_pay_bill', 10, 0);
            $table->string('bebas_pay_desc', 100)->nullable();
            $table->unsignedBigInteger('user_user_id')->nullable();
            $table->date('bebas_pay_input_date')->nullable();
            $table->timestamp('bebas_pay_last_update')->nullable();
            $table->string('bebas_merchantorder', 50)->nullable();
            $table->unsignedBigInteger('tabungan_tabungan_id')->nullable();

            // Foreign key
            $table->foreign('bebas_bebas_id')->references('bebas_id')->on('bebas')->onDelete('cascade');
            // $table->foreign('user_user_id')->references('user_id')->on('users'); // Uncomment jika ada tabel users
            // $table->foreign('tabungan_tabungan_id')->references('tabungan_id')->on('tabungan'); // Uncomment jika ada tabel tabungan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bebas_pay');
    }
};
