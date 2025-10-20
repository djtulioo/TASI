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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('channels')->onDelete('cascade');
            $table->string('sender_identifier')->index(); // Ex: número de telefone do usuário
            $table->text('message_body');
            $table->enum('direction', ['incoming', 'outgoing']); // Direção da mensagem
            $table->boolean('processed_by_ai')->default(false);
            $table->timestamps();
        });

        // Adiciona um campo na tabela 'channels' para associar ao número do WhatsApp
        Schema::table('channels', function (Blueprint $table) {
            $table->string('whatsapp_phone_id')->nullable()->after('name')->comment('ID do número de telefone registrado na API da Meta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('whatsapp_phone_id');
        });
        Schema::dropIfExists('conversations');
    }
};
