<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah school_id ke pos_pembayaran
        if (Schema::hasTable('pos_pembayaran') && !Schema::hasColumn('pos_pembayaran', 'school_id')) {
            Schema::table('pos_pembayaran', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->after('pos_id')
                    ->constrained('schools')->onDelete('cascade');
            });
        }

        // Tambah school_id ke payment
        if (Schema::hasTable('payment') && !Schema::hasColumn('payment', 'school_id')) {
            Schema::table('payment', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->after('payment_id')
                    ->constrained('schools')->onDelete('cascade');
            });
        }

        // Migrasi data existing: assign POS dan Payment ke sekolah pertama jika ada
        $defaultSchool = DB::table('schools')->orderBy('id')->first();
        if ($defaultSchool) {
            // Update POS tanpa school_id
            if (Schema::hasTable('pos_pembayaran') && Schema::hasColumn('pos_pembayaran', 'school_id')) {
                DB::table('pos_pembayaran')
                    ->whereNull('school_id')
                    ->update(['school_id' => $defaultSchool->id]);
            }

            // Update Payment tanpa school_id
            if (Schema::hasTable('payment') && Schema::hasColumn('payment', 'school_id')) {
                DB::table('payment')
                    ->whereNull('school_id')
                    ->update(['school_id' => $defaultSchool->id]);
            }
        }

        // Set NOT NULL setelah data di-update (jika semua data sudah punya school_id)
        if ($defaultSchool) {
            try {
                if (Schema::hasTable('pos_pembayaran') && Schema::hasColumn('pos_pembayaran', 'school_id')) {
                    // Hanya set NOT NULL jika tidak ada NULL values
                    $nullCount = DB::table('pos_pembayaran')->whereNull('school_id')->count();
                    if ($nullCount == 0) {
                        Schema::table('pos_pembayaran', function (Blueprint $table) {
                            $table->foreignId('school_id')->nullable(false)->change();
                        });
                    }
                }

                if (Schema::hasTable('payment') && Schema::hasColumn('payment', 'school_id')) {
                    $nullCount = DB::table('payment')->whereNull('school_id')->count();
                    if ($nullCount == 0) {
                        Schema::table('payment', function (Blueprint $table) {
                            $table->foreignId('school_id')->nullable(false)->change();
                        });
                    }
                }
            } catch (\Exception $e) {
                // Skip jika error
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos_pembayaran') && Schema::hasColumn('pos_pembayaran', 'school_id')) {
            Schema::table('pos_pembayaran', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }

        if (Schema::hasTable('payment') && Schema::hasColumn('payment', 'school_id')) {
            Schema::table('payment', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }
    }
};




