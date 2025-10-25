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
        Schema::create('transfer_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transfer_id')->unsigned();
            $table->tinyInteger('payment_type');
            $table->integer('bulan_id')->unsigned()->nullable();
            $table->integer('bebas_id')->unsigned()->nullable();
            $table->string('desc', 255)->nullable();
            $table->decimal('subtotal', 10, 0)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_detail');
    }
};
