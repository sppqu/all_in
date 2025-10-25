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
        Schema::create('log_tabungan', function (Blueprint $table) {
            $table->increments('log_tabungan_id');
            $table->integer('tabungan_tabungan_id')->unsigned();
            $table->integer('student_student_id')->unsigned();
            $table->double('kredit')->default(0);
            $table->double('debit')->default(0);
            $table->double('saldo')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamp('log_tabungan_input_date')->useCurrent();
            $table->timestamp('log_tabungan_last_update')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_tabungan');
    }
};
