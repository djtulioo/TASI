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
        Schema::create('feedback_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['demanda', 'sugestao', 'opiniao']);
            $table->string('titulo')->nullable();
            $table->text('descricao');
            $table->string('sender_identifier')->nullable(); // ID do usuário que enviou
            $table->enum('status', ['pendente', 'em_analise', 'resolvido', 'cancelado'])->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_entries');
    }
};
