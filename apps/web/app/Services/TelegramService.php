<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Gemini\Data\Content;
use Gemini\Enums\Role;

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

        // 1. Verifica se a mensagem já foi processada (evita duplicatas)
        $messageId = (string) $messageData['message_id'];
        $existingMessage = Conversation::where('whatsapp_message_id', $messageId)
            ->where('channel_id', $channel->id)
            ->first();

        if ($existingMessage) {
            Log::info("TelegramService: Mensagem já foi processada anteriormente (ID: {$messageId}). Ignorando.");
            return;
        }

        // 2. Salva a mensagem recebida no banco de dados
        $incomingConversation = Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => $senderId,
            'message_body' => $messageBody,
            'direction' => 'incoming',
            'bot_user_id' => $botToken,
            'whatsapp_message_id' => $messageId, // Usando o campo existente para o ID da mensagem do Telegram
        ]);
        Log::info("TelegramService: Mensagem salva no banco.");

        // 3. Busca histórico de conversação recente do usuário (últimas 10 mensagens)
        $recentConversations = Conversation::where('channel_id', $channel->id)
            ->where('sender_identifier', $senderId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse(); // Ordem cronológica

        // 4. Converte histórico para formato Gemini (objetos Content)
        $history = [];
        foreach ($recentConversations as $conv) {
            if ($conv->id === $incomingConversation->id) {
                continue; // Pula a mensagem atual (já será enviada como userMessage)
            }
            
            $role = $conv->direction === 'incoming' ? Role::USER : Role::MODEL;
            $history[] = Content::parse(part: $conv->message_body, role: $role);
        }

        // 5. Solicita uma resposta da IA com function calling (sistema de ouvidoria)
        Log::info("TelegramService: Chamando GeminiService com function calling...");
        try {
            $result = $this->geminiService->generateResponseWithFunctionCalling(
                userMessage: $messageBody,
                history: $history,
                channelId: $channel->id,
                senderIdentifier: $senderId,
                conversationId: $incomingConversation->id,
                botUserId: $botToken
            );
            
            $aiResponseText = $result['text'];
            Log::info("TelegramService: Resposta da IA gerada: {$aiResponseText}");

            // Se um feedback foi cadastrado, adiciona uma mensagem informativa
            if ($result['feedback_entry']) {
                $entry = $result['feedback_entry'];
                Log::info("TelegramService: Feedback cadastrado!", [
                    'id' => $entry->id,
                    'tipo' => $entry->tipo,
                    'sender' => $senderId
                ]);
            }
        } catch (\Exception $e) {
            Log::error("TelegramService: Erro ao gerar resposta da IA: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return;
        }

        // 6. Salva a resposta da IA como uma mensagem de saída
        Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => $senderId,
            'message_body' => $aiResponseText,
            'direction' => 'outgoing',
            'bot_user_id' => $botToken,
            'processed_by_ai' => true,
        ]);

        // 7. Envia a resposta de volta para o Telegram
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
    public function sendMessage(string $botToken, string $chatId, string $text)
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
    
    /**
     * Define o webhook para o bot do Telegram.
     *
     * @param string $botToken
     * @param string $url
     * @return array
     * @throws \Exception
     */
    public function setWebhook(string $botToken, string $url): array
    {
        $apiUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";

        try {
            $response = Http::withoutVerifying()->post($apiUrl, [
                'url' => $url,
            ]);

            if (!$response->successful()) {
                Log::error("Erro ao definir webhook do Telegram: " . $response->body());
                throw new \Exception("Falha ao configurar webhook no Telegram: " . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Erro ao definir webhook do Telegram: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca as informações do bot, incluindo avatar.
     *
     * @param string $botToken
     * @return array|null
     */
    public function getBotInfo(string $botToken): ?array
    {
        $apiUrl = "https://api.telegram.org/bot{$botToken}/getMe";

        try {
            $response = Http::withoutVerifying()->get($apiUrl);

            if ($response->successful()) {
                return $response->json()['result'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Erro ao buscar informações do bot: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Baixa o avatar do bot do Telegram.
     *
     * @param string $botToken
     * @return string|null Path to the saved avatar
     */
    public function downloadBotAvatar(string $botToken): ?string
    {
        try {
            // Get bot info
            $botInfo = $this->getBotInfo($botToken);
            
            if (!$botInfo) {
                return null;
            }

            // Get user profile photos
            $apiUrl = "https://api.telegram.org/bot{$botToken}/getUserProfilePhotos";
            $response = Http::withoutVerifying()->get($apiUrl, [
                'user_id' => $botInfo['id'],
                'limit' => 1
            ]);

            if (!$response->successful()) {
                return null;
            }

            $photos = $response->json()['result']['photos'] ?? [];
            
            if (empty($photos) || empty($photos[0])) {
                return null;
            }

            // Get the largest photo
            $photo = end($photos[0]);
            $fileId = $photo['file_id'];

            // Get file path
            $fileResponse = Http::withoutVerifying()->get("https://api.telegram.org/bot{$botToken}/getFile", [
                'file_id' => $fileId
            ]);

            if (!$fileResponse->successful()) {
                return null;
            }

            $filePath = $fileResponse->json()['result']['file_path'] ?? null;
            
            if (!$filePath) {
                return null;
            }

            // Download the file
            $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";
            $imageContent = Http::withoutVerifying()->get($fileUrl)->body();

            // Save to storage
            $fileName = 'avatars/telegram_' . md5($botToken) . '.jpg';
            Storage::disk('public')->put($fileName, $imageContent);

            return $fileName;
        } catch (\Exception $e) {
            Log::error("Erro ao baixar avatar do bot: " . $e->getMessage());
            return null;
        }
    }
}
