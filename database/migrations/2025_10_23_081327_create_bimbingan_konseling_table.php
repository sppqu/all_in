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
        Schema::create('bimbingan_konseling', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('student_id')->on('students')->onDelete('cascade');
            
            $table->string('jenis_bimbingan'); // akademik, pribadi, sosial, karir
            $table->string('kategori'); // ringan, sedang, berat
            $table->text('permasalahan');
            $table->text('analisis')->nullable();
            $table->text('tindakan')->nullable();
            $table->text('hasil')->nullable();
            $table->date('tanggal_bimbingan');
            $table->integer('sesi_ke')->default(1);
            $table->enum('status', ['dijadwalkan', 'berlangsung', 'selesai', 'ditunda'])->default('dijadwalkan');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('guru_bk_id'); // user yang handle
            $table->foreign('guru_bk_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimbingan_konseling');
    }
};
