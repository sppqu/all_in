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
        Schema::table('setup_gateways', function (Blueprint $table) {
            // Add school_id column if it doesn't exist
            if (!Schema::hasColumn('setup_gateways', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('setup_id')
                    ->constrained('schools')->onDelete('cascade');
            }
        });

        // Migrate existing data to first school (if any)
        $firstSchool = DB::table('schools')->orderBy('id')->first();
        if ($firstSchool) {
            DB::table('setup_gateways')
                ->whereNull('school_id')
                ->update(['school_id' => $firstSchool->id]);
        }

        // Make school_id NOT NULL after migration (if there are schools)
        if ($firstSchool && DB::table('setup_gateways')->whereNull('school_id')->count() === 0) {
            Schema::table('setup_gateways', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            if (Schema::hasColumn('setup_gateways', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });
    }
};
