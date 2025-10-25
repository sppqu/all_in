<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_pembayaran', function (Blueprint $table) {
            $table->id('pos_id');
            $table->string('pos_name', 100);
            $table->string('pos_description', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_pembayaran');
    }
}; 