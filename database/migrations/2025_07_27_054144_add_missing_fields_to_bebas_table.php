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
            $table->string('bebas_number_pay', 100)->nullable()->after('bebas_date_pay');
            $table->text('bebas_pay_desc')->nullable()->after('bebas_number_pay');
            $table->unsignedBigInteger('user_user_id')->nullable()->after('bebas_pay_desc');
            $table->string('bebas_merchantorder', 150)->nullable()->after('user_user_id');
            $table->string('nama_bank', 50)->nullable()->after('bebas_merchantorder');
            $table->string('va_bank', 50)->nullable()->after('nama_bank');
            $table->text('panduan_bank')->nullable()->after('va_bank');
            $table->dateTime('expired_date_pay')->nullable()->after('panduan_bank');
            $table->decimal('bebas_fee', 10, 0)->nullable()->after('expired_date_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bebas', function (Blueprint $table) {
            $table->dropColumn([
                'bebas_number_pay',
                'bebas_pay_desc',
                'user_user_id',
                'bebas_merchantorder',
                'nama_bank',
                'va_bank',
                'panduan_bank',
                'expired_date_pay',
                'bebas_fee'
            ]);
        });
    }
};
