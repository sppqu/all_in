<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setup_gateways', function (Blueprint $table) {
            $table->increments('setup_id');
            $table->string('url_duitku', 100)->nullable();
            $table->string('apikey_duitku', 50)->nullable();
            $table->string('merchantcode_duitku', 50)->nullable();
            $table->enum('duitku_sandbox', ['false', 'true'])->default('false');
            $table->string('url_tripay', 100)->nullable();
            $table->string('apikey_tripay', 50)->nullable();
            $table->string('privatekey_tripay', 50)->nullable();
            $table->string('merchantcode_tripay', 20)->nullable();
            $table->enum('payment_gateway', ['duitku', 'tripay'])->nullable();
            $table->string('url_wagateway', 100)->nullable();
            $table->string('apikey_wagateway', 100)->nullable();
            $table->string('sender_wagateway', 50)->nullable();
            $table->string('wa_gateway', 50)->nullable();
            $table->string('norek_bank', 100)->nullable();
            $table->string('nama_bank', 100)->nullable();
            $table->string('nama_rekening', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_gateways');
    }
};
