<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;
use App\Models\Channel;

class ConversationController extends Controller
{
    /**
     * Display the chat interface with a list of conversations.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam;
        
        if (!$team->last_selected_channel_id) {
            return redirect()->route('dashboard')->with('error', 'Selecione um canal primeiro.');
        }

        $channelId = $team->last_selected_channel_id;

        // Obter todos os IDs de canais relacionados ao mesmo Bot
        $channel = Channel::find($channelId);
        $botUserId = $channel ? ($channel->phone_number_id ?? $channel->telegram_bot_token) : null;

        $query = Conversation::query();
        if ($botUserId) {
            $query->where('bot_user_id', $botUserId);
        } else {
             // Fallback para canais antigos sem bot_id (ou lógica anterior)
             $query->where('channel_id', $channelId);
        }

        // Agrupar mensagens por sender_identifier para criar a lista de "conversas"
        // Pegamos a última mensagem de cada sender para mostrar no preview
        $conversations = $query->select('sender_identifier', DB::raw('MAX(created_at) as last_message_at'))
            ->groupBy('sender_identifier')
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($group) use ($botUserId, $channelId) {
                // Para cada grupo, pegamos os detalhes da última mensagem
                $msgQuery = Conversation::where('sender_identifier', $group->sender_identifier);

                if ($botUserId) {
                    $msgQuery->where('bot_user_id', $botUserId);
                } else {
                    $msgQuery->where('channel_id', $channelId);
                }

                $lastMessage = $msgQuery->orderBy('created_at', 'desc')->first();

                return [
                    'id' => $group->sender_identifier, // Usamos o sender_identifier como ID da conversa
                    'name' => $group->sender_identifier, // Por enquanto o nome é o ID (número)
                    'avatar' => '👤', // Avatar padrão
                    'lastMessage' => $lastMessage ? $lastMessage->message_body : '',
                    'timestamp' => $lastMessage ? $lastMessage->created_at->format('H:i') : '',
                    'raw_timestamp' => $lastMessage ? $lastMessage->created_at : null,
                ];
            });

        return Inertia::render('Chat', [
            'initialConversations' => $conversations,
            'channelId' => $channelId,
        ]);
    }

    /**
     * Fetch messages for a specific sender (conversation thread).
     */
    public function show(Request $request, string $senderId)
    {
        $user = $request->user();
        $team = $user->currentTeam;
        $channelId = $team->last_selected_channel_id;

        $channel = Channel::find($channelId);
        $botUserId = $channel ? ($channel->phone_number_id ?? $channel->telegram_bot_token) : null;

        $query = Conversation::where('sender_identifier', $senderId);

        if ($botUserId) {
            $query->where('bot_user_id', $botUserId);
        } else {
            $query->where('channel_id', $channelId);
        }

        $messages = $query->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'text' => $msg->message_body,
                    'direction' => $msg->direction === 'outgoing' ? 'out' : 'in',
                    'timestamp' => $msg->created_at->format('H:i'),
                ];
            });

        return response()->json($messages);
    }
    /**
     * Send a message to the conversation.
     */
    public function store(Request $request, string $senderId, TelegramService $telegramService)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $team = $user->currentTeam;
        $channelId = $team->last_selected_channel_id;
        
        $channel = Channel::findOrFail($channelId);

        // Salvar mensagem no banco com bot_user_id
        $botUserId = $channel->phone_number_id ?? $channel->telegram_bot_token;

        $conversation = Conversation::create([
            'channel_id' => $channelId,
            'bot_user_id' => $botUserId,
            'sender_identifier' => $senderId,
            'message_body' => $request->message,
            'direction' => 'outgoing',
            'processed_by_ai' => false, // Mensagem manual
        ]);

        // Enviar para a API correspondente
        if ($channel->type === 'telegram') {
            if ($channel->telegram_bot_token) {
                $telegramService->sendMessage(
                    $channel->telegram_bot_token,
                    $senderId, // No Telegram, senderId é o chatId
                    $request->message
                );
            }
        } 
        // TODO: Implementar envio para WhatsApp
        // else if ($channel->type === 'whatsapp') { ... }

        return response()->json([
            'id' => $conversation->id,
            'text' => $conversation->message_body,
            'direction' => 'out',
            'timestamp' => $conversation->created_at->format('H:i'),
        ]);
    }
}
