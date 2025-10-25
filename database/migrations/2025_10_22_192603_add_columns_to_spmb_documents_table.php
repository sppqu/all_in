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
        Schema::table('spmb_documents', function (Blueprint $table) {
            $table->foreignId('registration_id')->after('id')->constrained('spmb_registrations')->onDelete('cascade');
            $table->string('document_type')->after('registration_id');
            $table->string('file_name')->after('document_type');
            $table->string('file_path')->after('file_name');
            $table->integer('file_size')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('file_size');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('mime_type');
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_documents', function (Blueprint $table) {
            $table->dropForeign(['registration_id']);
            $table->dropColumn([
                'registration_id',
                'document_type',
                'file_name',
                'file_path',
                'file_size',
                'mime_type',
                'status',
                'notes'
            ]);
        });
    }
};
