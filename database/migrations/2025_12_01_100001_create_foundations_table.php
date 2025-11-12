<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('foundations')) {
            Schema::create('foundations', function (Blueprint $table) {
                $table->id();
                $table->string('nama_yayasan', 255);
                $table->text('alamat_yayasan')->nullable();
                $table->string('no_telp_yayasan', 50)->nullable();
                $table->string('logo_yayasan', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('foundations');
    }
};





