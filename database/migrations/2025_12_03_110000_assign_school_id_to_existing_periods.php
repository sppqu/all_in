<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Assign school_id ke periode yang belum punya school_id
        $defaultSchool = DB::table('schools')->orderBy('id')->first();
        if ($defaultSchool) {
            DB::table('periods')
                ->whereNull('school_id')
                ->update(['school_id' => $defaultSchool->id]);
        }
    }

    public function down(): void
    {
        // Tidak perlu rollback karena ini adalah data migration
    }
};
