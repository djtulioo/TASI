<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;

class TelegramWebhookController extends Controller
{
    /**
     * Lida com as notificações recebidas do Telegram.
     *
     * @param Request $request
     * @param string $botToken
     * @param TelegramService $telegramService
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, string $botToken, TelegramService $telegramService)
    {
        $payload = $request->all();
        Log::info('Webhook do Telegram recebido:', ['bot_token_prefix' => substr($botToken, 0, 5) . '...', 'payload' => $payload]);

        try {
            // Delega o processamento da mensagem para o service
            $telegramService->handleIncomingMessage($payload, $botToken);

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do Telegram: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Retorna 200 para evitar reenvios infinitos do Telegram em caso de erro interno
            return response()->json(['status' => 'error'], 200);
        }
    }
}
