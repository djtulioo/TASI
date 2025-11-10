<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class WebhookController extends Controller
{
    /**
     * Verifica o endpoint do webhook.
     * A API do WhatsApp envia uma requisição GET para este endpoint com um token de verificação.
     */
    public function verify(Request $request)
    {
        $verifyToken = env('WHATSAPP_VERIFY_TOKEN');

        if (!$verifyToken) {
            Log::error('A variável de ambiente WHATSAPP_VERIFY_TOKEN não está definida.');
            return response('Erro de configuração no servidor.', 500);
        }

        if ($request->hub_mode === 'subscribe' && $request->hub_verify_token === $verifyToken) {
            Log::info("Webhook verificado com sucesso!");
            return response($request->hub_challenge, 200);
        }

        Log::warning("Falha na verificação do Webhook.", $request->all());
        return response('Token de verificação inválido', 403);
    }

    /**
     * Lida com as notificações recebidas do WhatsApp.
     */
    public function handle(Request $request, WhatsAppService $whatsAppService)
    {
        $payload = $request->all();
        Log::info('Webhook do WhatsApp recebido:', $payload);

        try {
            // Delega o processamento da mensagem para o service
            $whatsAppService->handleIncomingMessage($payload);

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do WhatsApp: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Retorna 200 mesmo em caso de erro para evitar que a API do WhatsApp
            // desative o webhook por falhas repetidas. O erro já foi logado.
            return response()->json(['status' => 'error'], 200);
        }
    }
}
