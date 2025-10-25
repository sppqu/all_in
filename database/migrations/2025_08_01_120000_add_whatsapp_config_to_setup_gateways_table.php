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
            // Tambahkan kolom untuk konfigurasi WhatsApp jika belum ada
            if (!Schema::hasColumn('setup_gateways', 'url_wagateway')) {
                $table->string('url_wagateway', 255)->nullable()->after('merchantcode_tripay');
            }
            if (!Schema::hasColumn('setup_gateways', 'apikey_wagateway')) {
                $table->string('apikey_wagateway', 255)->nullable()->after('url_wagateway');
            }
            if (!Schema::hasColumn('setup_gateways', 'sender_wagateway')) {
                $table->string('sender_wagateway', 50)->nullable()->after('apikey_wagateway');
            }
            if (!Schema::hasColumn('setup_gateways', 'wa_gateway')) {
                $table->string('wa_gateway', 50)->nullable()->after('sender_wagateway');
            }
            
            // Tambahkan kolom untuk mengaktifkan notifikasi WhatsApp
            if (!Schema::hasColumn('setup_gateways', 'enable_wa_notification')) {
                $table->boolean('enable_wa_notification')->default(false)->after('wa_gateway');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            $table->dropColumn([
                'url_wagateway',
                'apikey_wagateway', 
                'sender_wagateway',
                'wa_gateway',
                'enable_wa_notification'
            ]);
        });
    }
}; 