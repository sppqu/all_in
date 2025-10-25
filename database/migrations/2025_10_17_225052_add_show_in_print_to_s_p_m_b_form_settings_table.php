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
        Schema::table('s_p_m_b_form_settings', function (Blueprint $table) {
            $table->boolean('show_in_print')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_p_m_b_form_settings', function (Blueprint $table) {
            $table->dropColumn('show_in_print');
        });
    }
};
