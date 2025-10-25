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
        Schema::create('transfer', function (Blueprint $table) {
            $table->increments('transfer_id');
            $table->integer('student_id')->unsigned();
            $table->string('detail', 255)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('confirm_name', 50)->nullable();
            $table->string('confirm_bank', 50)->nullable();
            $table->string('confirm_accnum', 50)->nullable();
            $table->string('confirm_photo', 255)->nullable();
            $table->decimal('confirm_pay', 10, 0)->nullable();
            $table->dateTime('confirm_date')->nullable();
            $table->integer('verif_user')->unsigned()->nullable();
            $table->dateTime('verif_date')->nullable();
            $table->tinyInteger('is_out')->default(0);
            $table->string('checkout_url', 100)->nullable();
            $table->string('merchantRef', 100)->nullable();
            $table->string('reference', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer');
    }
};
