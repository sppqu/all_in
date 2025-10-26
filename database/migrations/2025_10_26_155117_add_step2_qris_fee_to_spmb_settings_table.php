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
        Schema::table('s_p_m_b_settings', function (Blueprint $table) {
            $table->decimal('step2_qris_fee', 10, 2)->default(0)->after('biaya_pendaftaran')->comment('Biaya tambahan QRIS Step-2 (ditambahkan ke Rp 3.000)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_p_m_b_settings', function (Blueprint $table) {
            $table->dropColumn('step2_qris_fee');
        });
    }
};
