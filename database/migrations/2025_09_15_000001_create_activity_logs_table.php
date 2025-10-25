<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('time')->useCurrent();
            $table->string('actor_type', 20)->nullable(); // user|student|system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 50); // login, logout, create, update, delete, pay, export, etc.
            $table->string('context', 100)->nullable(); // module/feature
            $table->string('message', 255)->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            $table->index(['actor_type','actor_id']);
            $table->index(['time']);
            $table->index(['context']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};


