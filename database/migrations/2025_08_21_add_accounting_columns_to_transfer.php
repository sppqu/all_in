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
            // Kolom untuk tracking accounting
            $table->boolean('accounting_processed')->default(false)->after('status');
            $table->timestamp('accounting_processed_at')->nullable()->after('accounting_processed');
            
            // Index untuk optimasi query
            $table->index(['status', 'accounting_processed']);
            $table->index('accounting_processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer', function (Blueprint $table) {
            $table->dropIndex(['status', 'accounting_processed']);
            $table->dropIndex('accounting_processed_at');
            $table->dropColumn(['accounting_processed', 'accounting_processed_at']);
        });
    }
};
