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
        Schema::table('bulan', function (Blueprint $table) {
            $table->decimal('bulan_pay', 10, 0)->default(0)->after('bulan_bill');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulan', function (Blueprint $table) {
            $table->dropColumn('bulan_pay');
        });
    }
};
