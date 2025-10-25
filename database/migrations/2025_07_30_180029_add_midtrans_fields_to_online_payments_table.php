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
        Schema::table('online_payments', function (Blueprint $table) {
            // Midtrans specific fields
            $table->string('reference')->nullable()->after('payment_number');
            $table->string('payment_type')->nullable()->after('reference');
            $table->string('period')->nullable()->after('payment_type');
            $table->text('description')->nullable()->after('period');
            $table->json('payment_data')->nullable()->after('description');
            $table->text('snap_token')->nullable()->after('payment_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_payments', function (Blueprint $table) {
            $table->dropColumn([
                'reference',
                'payment_type',
                'period',
                'description',
                'payment_data',
                'snap_token'
            ]);
        });
    }
};
