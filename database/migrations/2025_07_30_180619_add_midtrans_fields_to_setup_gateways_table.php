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
        Schema::table('setup_gateways', function (Blueprint $table) {
            // Midtrans configuration fields
            $table->string('midtrans_server_key')->nullable()->after('nama_rekening');
            $table->string('midtrans_client_key')->nullable()->after('midtrans_server_key');
            $table->string('midtrans_merchant_id')->nullable()->after('midtrans_client_key');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_merchant_id');
            $table->string('midtrans_snap_url')->nullable()->after('midtrans_is_production');
            $table->string('midtrans_api_url')->nullable()->after('midtrans_snap_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_server_key',
                'midtrans_client_key',
                'midtrans_merchant_id',
                'midtrans_is_production',
                'midtrans_snap_url',
                'midtrans_api_url'
            ]);
        });
    }
};
