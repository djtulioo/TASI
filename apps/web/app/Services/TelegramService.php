<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Processa a carga útil (payload) de uma mensagem recebida do Telegram.
     *
     * @param array $payload
     * @param string $botToken
     * @return void
     */
    public function handleIncomingMessage(array $payload, string $botToken)
    {
        // Verifica se é uma mensagem de texto
        if (!isset($payload['message']) || !isset($payload['message']['text'])) {
            Log::info('Webhook Telegram recebido, mas não é uma mensagem de texto ou está vazio.');
            return;
        }

        $messageData = $payload['message'];
        $chatId = $messageData['chat']['id'];
        $messageBody = $messageData['text'];
        $senderId = (string) $chatId; // No Telegram, o senderId é o chatId para conversas privadas

        Log::info("TelegramService: Iniciando processamento da mensagem.");

        // Encontra o canal pelo bot token
        $channel = Channel::where('telegram_bot_token', $botToken)->first();

        if (!$channel) {
            Log::warning("Nenhum canal encontrado para o bot token fornecido.");
            return;
        }
        Log::info("TelegramService: Canal encontrado: {$channel->name}");

        // 1. Salva a mensagem recebida no banco de dados
        $incomingConversation = Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => $senderId,
            'message_body' => $messageBody,
            'direction' => 'incoming',
            'whatsapp_message_id' => (string) $messageData['message_id'], // Usando o campo existente para o ID da mensagem do Telegram
        ]);
        Log::info("TelegramService: Mensagem salva no banco.");

        // 2. Solicita uma resposta da IA
        Log::info("TelegramService: Chamando GeminiService...");
        try {
            $aiResponseText = $this->geminiService->generateResponse($messageBody);
            Log::info("TelegramService: Resposta da IA gerada: {$aiResponseText}");
        } catch (\Exception $e) {
            Log::error("TelegramService: Erro ao gerar resposta da IA: " . $e->getMessage());
            return;
        }

        // 3. Salva a resposta da IA como uma mensagem de saída
        Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => $senderId,
            'message_body' => $aiResponseText,
            'direction' => 'outgoing',
            'processed_by_ai' => true,
        ]);

        // 4. Envia a resposta de volta para o Telegram
        Log::info("TelegramService: Enviando resposta para o Telegram...");
        $this->sendMessage($botToken, $chatId, $aiResponseText);
        
        Log::info("TelegramService: Processo finalizado.");
    }

    /**
     * Envia uma mensagem para o Telegram.
     *
     * @param string $botToken
     * @param string $chatId
     * @param string $text
     * @return void
     */
    protected function sendMessage(string $botToken, string $chatId, string $text)
    {
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            $response = Http::withoutVerifying()->post($url, [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

            if (!$response->successful()) {
                Log::error("Erro ao enviar mensagem para o Telegram: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exceção ao enviar mensagem para o Telegram: " . $e->getMessage());
        }
    }
}
