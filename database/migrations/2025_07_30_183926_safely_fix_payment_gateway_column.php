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
        // First, clear any existing data in payment_gateway column
        \DB::table('setup_gateways')->update(['payment_gateway' => '']);
        
        Schema::table('setup_gateways', function (Blueprint $table) {
            // Change payment_gateway column to VARCHAR(50) to accommodate longer gateway names
            $table->string('payment_gateway', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            // Revert back to original size
            $table->string('payment_gateway', 10)->nullable()->change();
        });
    }
};
