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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('official_whatsapp_number');
            $table->string('app_id');
            $table->string('app_secret');
            $table->text('access_token');
            $table->string('phone_number_id');
            $table->json('other_api_params')->nullable();
            $table->json('chatbot_config')->nullable();
            $table->timestamps();

            // Índices para melhor performance
            $table->index('team_id');
            $table->index('phone_number_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};

