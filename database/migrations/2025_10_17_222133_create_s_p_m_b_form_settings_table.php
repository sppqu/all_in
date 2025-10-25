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
        Schema::create('s_p_m_b_form_settings', function (Blueprint $table) {
            $table->id();
            $table->string('field_name')->unique();
            $table->string('field_label');
            $table->string('field_type')->default('text'); // text, textarea, select, date, etc
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('field_options')->nullable(); // untuk select options
            $table->string('field_placeholder')->nullable();
            $table->text('field_help_text')->nullable();
            $table->integer('field_order')->default(0);
            $table->string('field_section')->default('personal'); // personal, parent, academic
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_p_m_b_form_settings');
    }
};
