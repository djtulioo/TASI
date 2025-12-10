<?php

namespace App\Http\Controllers;

use App\Models\FeedbackEntry;
use App\Models\Channel;
use App\Models\Conversation;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class FeedbackEntryController extends Controller
{
    /**
     * Display a listing of feedback entries.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $currentTeam = $user->currentTeam;

        // Se não houver canal atual, redirecionar para criar canal
        // Se não houver canal atual, tentar usar o primeiro disponível
        if (!$currentTeam->last_selected_channel_id && !$currentTeam->channels()->exists()) {
            return redirect()->route('channels.create');
        }

        $currentChannel = $currentTeam->lastSelectedChannel ?? $currentTeam->channels()->first();
        $botUserId = $currentChannel ? ($currentChannel->phone_number_id ?? $currentChannel->telegram_bot_token) : null;

        $query = FeedbackEntry::with(['channel', 'conversation']);

        if ($botUserId) {
            $query->where('bot_user_id', $botUserId);
        } elseif ($currentChannel) {
             $query->where('channel_id', $currentChannel->id);
        } else {
             $query->where('id', -1); // Fallback safe
        }

        // Filtrar por tipo se fornecido
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtrar por status se fornecido
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $feedbackEntries = $query->orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('FeedbackEntries/Index', [
            'feedbackEntries' => $feedbackEntries,
            'filters' => $request->only(['tipo', 'status']),
        ]);
    }

    /**
     * Display the specified feedback entry.
     */
    public function show(FeedbackEntry $feedbackEntry)
    {
        return Inertia::render('FeedbackEntries/Show', [
            'feedbackEntry' => $feedbackEntry->load(['channel', 'conversation']),
        ]);
    }

    /**
     * Update the specified feedback entry.
     */
    public function update(Request $request, FeedbackEntry $feedbackEntry)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|in:pendente,em_analise,resolvido,cancelado',
            'titulo' => 'sometimes|nullable|string|max:255',
        ]);

        $feedbackEntry->update($validated);

        return Redirect::back()->with('success', 'Feedback atualizado com sucesso!');
    }

    /**
     * Remove the specified feedback entry.
     */
    public function destroy(FeedbackEntry $feedbackEntry)
    {
        $feedbackEntry->delete();

        return Redirect::route('feedback-entries.index')->with('success', 'Feedback excluído com sucesso!');
    }

    /**
     * Process a message with Gemini AI and potentially create feedback entry.
     * Este método pode ser usado via API para processar mensagens de chat.
     */
    public function processMessage(Request $request, GeminiService $geminiService)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'channel_id' => 'required|integer|exists:channels,id',
            'sender_identifier' => 'nullable|string',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
            'history' => 'nullable|array', // Histórico de mensagens
        ]);

        $channel = Channel::findOrFail($validated['channel_id']);
        $botUserId = $channel->phone_number_id ?? $channel->telegram_bot_token;

        // Salvar a mensagem do usuário na conversa
        $botUserId = $channel->phone_number_id ?? $channel->telegram_bot_token;

        if (isset($validated['conversation_id'])) {
            $conversation = Conversation::find($validated['conversation_id']);
        } else {
            // Criar nova conversa se não existir
            $conversation = Conversation::create([
                'channel_id' => $validated['channel_id'],
                'bot_user_id' => $botUserId,
                'sender_identifier' => $validated['sender_identifier'] ?? 'unknown',
                'message_body' => $validated['message'],
                'direction' => 'inbound',
                'processed_by_ai' => false,
            ]);
        }

        // Processar com Gemini usando function calling
        $result = $geminiService->generateResponseWithFunctionCalling(
            userMessage: $validated['message'],
            history: $validated['history'] ?? [],
            channelId: $validated['channel_id'],
            senderIdentifier: $validated['sender_identifier'] ?? null,
            conversationId: $conversation->id
        );

        // Salvar a resposta da IA na conversa
        if ($result['text']) {
            Conversation::create([
                'channel_id' => $validated['channel_id'],
                'bot_user_id' => $botUserId,
                'sender_identifier' => 'bot',
                'message_body' => $result['text'],
                'direction' => 'outbound',
                'processed_by_ai' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'response' => $result['text'],
            'history' => $result['history'],
            'feedback_entry' => $result['feedback_entry'],
            'conversation_id' => $conversation->id,
        ]);
    }
}
