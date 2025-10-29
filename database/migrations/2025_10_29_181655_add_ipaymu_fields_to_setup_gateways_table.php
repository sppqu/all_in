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
            // iPaymu configuration fields
            $table->string('ipaymu_va', 255)->nullable()->after('nama_rekening')->comment('iPaymu VA Key');
            $table->string('ipaymu_api_key', 255)->nullable()->after('ipaymu_va')->comment('iPaymu API Key');
            $table->enum('ipaymu_mode', ['sandbox', 'production'])->default('sandbox')->after('ipaymu_api_key')->comment('iPaymu Mode');
            $table->boolean('ipaymu_is_active')->default(false)->after('ipaymu_mode')->comment('iPaymu Active Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            $table->dropColumn([
                'ipaymu_va',
                'ipaymu_api_key',
                'ipaymu_mode',
                'ipaymu_is_active'
            ]);
        });
    }
};
