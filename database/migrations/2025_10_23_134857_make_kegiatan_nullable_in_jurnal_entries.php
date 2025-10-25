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
        Schema::table('jurnal_entries', function (Blueprint $table) {
            // Make kegiatan nullable (not used in new format)
            $table->text('kegiatan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            // Revert kegiatan to not nullable
            $table->text('kegiatan')->nullable(false)->change();
        });
    }
};
