<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulan', function (Blueprint $table) {
            $table->id('bulan_id');
            $table->unsignedBigInteger('student_student_id');
            $table->unsignedBigInteger('payment_payment_id');
            $table->unsignedBigInteger('month_month_id');
            $table->decimal('bulan_bill', 10, 0);
            $table->tinyInteger('bulan_status')->default(1);
            $table->text('bulan_pay_desc')->nullable();
            $table->string('bulan_number_pay', 100)->nullable();
            $table->date('bulan_date_pay')->nullable();
            $table->unsignedBigInteger('user_user_id')->nullable();
            $table->timestamp('bulan_input_date')->nullable();
            $table->timestamp('bulan_last_update')->nullable();
            $table->string('bulan_merchantorder', 150)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('va_bank', 50)->nullable();
            $table->text('panduan_bank')->nullable();
            $table->dateTime('expired_date_pay')->nullable();
            $table->decimal('bulan_fee', 10, 0)->nullable();
            $table->unsignedBigInteger('tabungan_tabungan_id')->nullable();

            // Foreign keys
            $table->foreign('student_student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('payment_payment_id')->references('payment_id')->on('payment')->onDelete('cascade');
            // $table->foreign('month_month_id')->references('month_id')->on('months'); // Uncomment jika ada tabel months
            // $table->foreign('user_user_id')->references('user_id')->on('users'); // Uncomment jika ada tabel users
            // $table->foreign('tabungan_tabungan_id')->references('tabungan_id')->on('tabungan'); // Uncomment jika ada tabel tabungan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulan');
    }
};
