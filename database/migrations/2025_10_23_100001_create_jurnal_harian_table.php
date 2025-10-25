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
        Schema::create('jurnal_harian', function (Blueprint $table) {
            $table->id('jurnal_id');
            $table->foreignId('siswa_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['draft', 'submitted', 'verified', 'revised'])->default('draft');
            $table->text('catatan_umum')->nullable();
            $table->text('refleksi')->nullable(); // Refleksi siswa tentang hari ini
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan_guru')->nullable();
            $table->timestamps();

            // Index
            $table->unique(['siswa_id', 'tanggal']); // One jurnal per student per day
            $table->index(['tanggal', 'status']);
            $table->index('siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_harian');
    }
};

