<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_schools')) {
            Schema::create('user_schools', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->string('role', 50)->default('staff');
                $table->timestamps();

                $table->unique(['user_id', 'school_id']);
                $table->index('user_id');
                $table->index('school_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_schools');
    }
};





