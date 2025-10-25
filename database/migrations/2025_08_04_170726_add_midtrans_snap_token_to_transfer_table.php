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
        Schema::table('transfer', function (Blueprint $table) {
            $table->string('midtrans_snap_token')->nullable()->after('checkout_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer', function (Blueprint $table) {
            $table->dropColumn('midtrans_snap_token');
        });
    }
};
