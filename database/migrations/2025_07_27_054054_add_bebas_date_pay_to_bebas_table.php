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
        Schema::table('bebas', function (Blueprint $table) {
            $table->date('bebas_date_pay')->nullable()->after('bebas_total_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bebas', function (Blueprint $table) {
            $table->dropColumn('bebas_date_pay');
        });
    }
};
