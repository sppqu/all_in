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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('book_categories')->onDelete('cascade');
            $table->string('judul');
            $table->string('pengarang');
            $table->string('penerbit')->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->string('isbn', 50)->nullable()->unique();
            $table->text('deskripsi')->nullable();
            $table->string('cover_image')->nullable(); // Path to cover image
            $table->string('file_path')->nullable(); // Path to PDF file
            $table->integer('jumlah_halaman')->nullable();
            $table->string('bahasa', 50)->default('Indonesia');
            $table->enum('status', ['tersedia', 'tidak_tersedia'])->default('tersedia');
            $table->integer('total_views')->default(0);
            $table->integer('total_downloads')->default(0);
            $table->integer('total_loans')->default(0);
            $table->boolean('is_featured')->default(false); // Buku unggulan
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes(); // Soft delete untuk archive
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
