<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable strict mode temporarily for this migration
        $originalSqlMode = DB::selectOne("SELECT @@sql_mode as mode")->mode;
        DB::statement("SET SESSION sql_mode=''");
        
        try {
            // Check if column already exists
            if (!Schema::hasColumn('subscription_invoices', 'payment_reference')) {
                Schema::table('subscription_invoices', function (Blueprint $table) {
                    $table->string('payment_reference')->nullable()->after('midtrans_transaction_id');
                });
            }
        } finally {
            // Restore original SQL mode
            DB::statement("SET SESSION sql_mode='{$originalSqlMode}'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->dropColumn('payment_reference');
        });
    }
};
