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
        Schema::create('tabungan', function (Blueprint $table) {
            $table->increments('tabungan_id');
            $table->integer('student_student_id')->unsigned();
            $table->integer('user_user_id')->unsigned();
            $table->double('saldo')->default(0);
            $table->timestamp('tabungan_input_date')->useCurrent();
            $table->timestamp('tabungan_last_update')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabungan');
    }
};
