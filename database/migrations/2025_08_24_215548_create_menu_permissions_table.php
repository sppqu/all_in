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
        Schema::create('menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // admin, superadmin, etc.
            $table->string('menu_key'); // menu.data_master, menu.pembayaran, etc.
            $table->boolean('allowed')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['role', 'menu_key']);
            $table->index('role');
            $table->index('menu_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_permissions');
    }
};
