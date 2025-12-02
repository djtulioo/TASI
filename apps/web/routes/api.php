<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Integração WhatsApp ---
// Rota para a verificação inicial do Webhook (exigido pela Meta/WhatsApp)
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify']);

// Rota para receber as mensagens e eventos do WhatsApp
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle']);

// --- Integração Telegram ---
// Rota para receber as mensagens e eventos do Telegram
// O token do bot é passado na URL para identificar o canal
Route::post('/webhook/telegram/{bot_token}', [\App\Http\Controllers\TelegramWebhookController::class, 'handle'])->name('api.webhook.telegram');
