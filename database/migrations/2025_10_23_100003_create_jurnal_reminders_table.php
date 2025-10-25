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
        Schema::create('jurnal_reminders', function (Blueprint $table) {
            $table->id('reminder_id');
            $table->foreignId('siswa_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->date('tanggal_reminder');
            $table->time('waktu_reminder')->default('19:00:00'); // Default 7 PM
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('pesan')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Index
            $table->index(['tanggal_reminder', 'status']);
            $table->index('siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_reminders');
    }
};

