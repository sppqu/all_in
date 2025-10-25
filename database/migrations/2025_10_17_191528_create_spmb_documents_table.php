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
        Schema::create('spmb_documents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            // Note: Additional columns are added via migration: 
            // 2025_10_22_192603_add_columns_to_spmb_documents_table.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_documents');
    }
};
