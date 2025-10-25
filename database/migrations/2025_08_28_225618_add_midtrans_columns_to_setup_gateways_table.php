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
            // Add midtrans_mode column if it doesn't exist
            if (!Schema::hasColumn('setup_gateways', 'midtrans_mode')) {
                $table->enum('midtrans_mode', ['sandbox', 'production'])->default('sandbox')->after('wa_gateway');
            }
            
            // Add midtrans server key columns if they don't exist
            if (!Schema::hasColumn('setup_gateways', 'midtrans_server_key_sandbox')) {
                $table->string('midtrans_server_key_sandbox')->nullable()->after('midtrans_mode');
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_client_key_sandbox')) {
                $table->string('midtrans_client_key_sandbox')->nullable()->after('midtrans_server_key_sandbox');
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_merchant_id_sandbox')) {
                $table->string('midtrans_merchant_id_sandbox')->nullable()->after('midtrans_client_key_sandbox');
            }
            
            // Add midtrans production key columns if they don't exist
            if (!Schema::hasColumn('setup_gateways', 'midtrans_server_key_production')) {
                $table->string('midtrans_server_key_production')->nullable()->after('midtrans_merchant_id_sandbox');
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_client_key_production')) {
                $table->string('midtrans_client_key_production')->nullable()->after('midtrans_server_key_production');
            }
            if (!Schema::hasColumn('setup_gateways', 'midtrans_merchant_id_production')) {
                $table->string('midtrans_merchant_id_production')->nullable()->after('midtrans_client_key_production');
            }
            
            // Add midtrans is_active column if it doesn't exist
            if (!Schema::hasColumn('setup_gateways', 'midtrans_is_active')) {
                $table->boolean('midtrans_is_active')->default(false)->after('midtrans_merchant_id_production');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            // Drop the columns we added
            $columnsToDrop = [
                'midtrans_mode',
                'midtrans_server_key_sandbox',
                'midtrans_client_key_sandbox',
                'midtrans_merchant_id_sandbox',
                'midtrans_server_key_production',
                'midtrans_client_key_production',
                'midtrans_merchant_id_production',
                'midtrans_is_active'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('setup_gateways', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
