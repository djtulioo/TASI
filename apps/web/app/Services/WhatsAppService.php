<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Processa a carga útil (payload) de uma mensagem recebida.
     *
     * @param array $payload
     * @return void
     */
    public function handleIncomingMessage(array $payload)
    {
        // Extrai a primeira mensagem do payload
        $messageData = $payload['entry'][0]['changes'][0]['value']['messages'][0] ?? null;

        if (!$messageData || $messageData['type'] !== 'text') {
            Log::info('Webhook recebido, mas não é uma mensagem de texto ou está vazio.');
            return;
        }

        $senderId = $messageData['from'];
        $messageBody = $messageData['text']['body'];
        $businessPhoneNumberId = $payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];

        // Lógica para encontrar o canal associado (pode precisar de ajuste)
        // Aqui, assumimos que o ID do número de telefone do negócio está salvo no canal.
        $channel = Channel::where('whatsapp_phone_id', $businessPhoneNumberId)->first();

        if (!$channel) {
            Log::warning("Nenhum canal encontrado para o whatsapp_phone_id: {$businessPhoneNumberId}");
            return;
        }

        // 1. Salva a mensagem recebida no banco de dados
        $incomingConversation = Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => $senderId,
            'message_body' => $messageBody,
            'direction' => 'incoming',
        ]);

        // 2. Solicita uma resposta da IA
        $aiResponseText = $this->geminiService->generateResponse($messageBody);

        // 3. Salva a resposta da IA como uma mensagem de saída
        Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => $senderId, // Mantém o identificador para agrupar a conversa
            'message_body' => $aiResponseText,
            'direction' => 'outgoing',
            'processed_by_ai' => true,
        ]);

        // 4. Aqui você adicionaria a lógica para ENVIAR a $aiResponseText de volta para o usuário
        // usando a API do WhatsApp. Ex: (new WhatsAppClient())->sendMessage($senderId, $aiResponseText);
        Log::info("Resposta da IA gerada para {$senderId}: {$aiResponseText}");
    }
}
