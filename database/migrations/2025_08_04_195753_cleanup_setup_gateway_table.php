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
            // Hapus field yang benar-benar ada di tabel
            $columnsToDrop = [];
            
            // Cek dan hapus kolom yang ada
            if (Schema::hasColumn('setup_gateways', 'midtrans_server_key')) {
                $columnsToDrop[] = 'midtrans_server_key';
            }
            if (Schema::hasColumn('setup_gateways', 'midtrans_client_key')) {
                $columnsToDrop[] = 'midtrans_client_key';
            }
            if (Schema::hasColumn('setup_gateways', 'midtrans_merchant_id')) {
                $columnsToDrop[] = 'midtrans_merchant_id';
            }
            if (Schema::hasColumn('setup_gateways', 'midtrans_is_production')) {
                $columnsToDrop[] = 'midtrans_is_production';
            }
            if (Schema::hasColumn('setup_gateways', 'midtrans_snap_url')) {
                $columnsToDrop[] = 'midtrans_snap_url';
            }
            if (Schema::hasColumn('setup_gateways', 'midtrans_api_url')) {
                $columnsToDrop[] = 'midtrans_api_url';
            }
            
            // Hapus kolom yang ada
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            // Restore field yang dihapus (hanya yang benar-benar ada)
            if (!Schema::hasColumn('setup_gateways', 'midtrans_server_key')) {
                $table->string('midtrans_server_key')->nullable();
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_client_key')) {
                $table->string('midtrans_client_key')->nullable();
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_merchant_id')) {
                $table->string('midtrans_merchant_id')->nullable();
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_is_production')) {
                $table->boolean('midtrans_is_production')->default(false);
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_snap_url')) {
                $table->string('midtrans_snap_url')->nullable();
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_api_url')) {
                $table->string('midtrans_api_url')->nullable();
            }
        });
    }
};
