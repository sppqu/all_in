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
        Schema::create('log_trx', function (Blueprint $table) {
            $table->increments('log_trx_id');
            $table->integer('student_student_id')->unsigned();
            $table->integer('bulan_bulan_id')->unsigned()->nullable();
            $table->integer('bebas_pay_bebas_pay_id')->unsigned()->nullable();
            $table->timestamp('log_trx_input_date')->useCurrent();
            $table->timestamp('log_trx_last_update')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_trx');
    }
}; 