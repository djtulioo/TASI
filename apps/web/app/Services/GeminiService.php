<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
// use Gemini\Laravel\Facades\Gemini; // Removido pois o pacote laravel não está instalado
use Gemini\Data\Content;
use Gemini\Enums\Role;

class GeminiService
{
    /**
     * Gera uma resposta usando a IA do Gemini.
     *
     * @param string $prompt
     * @return string
     */
    public function generateResponse(string $prompt): string
    {
        try {
            // Lógica do prompt do sistema para dar contexto à IA
            $systemPrompt = "Você é um assistente de ouvidoria para a plataforma Pulsar. Responda de forma concisa e útil, ajudando o usuário a registrar seu feedback.";

            $apiKey = env('GEMINI_API_KEY');
            if (!$apiKey) {
                throw new \Exception('GEMINI_API_KEY não definida.');
            }
            Log::info("GeminiService: Usando chave API iniciando com: " . substr($apiKey, 0, 5));

            $guzzle = new \GuzzleHttp\Client(['verify' => false]);
            $client = \Gemini::factory()
                ->withApiKey($apiKey)
                ->withHttpClient($guzzle)
                ->make();

            $result = $client->generativeModel(model: 'gemini-2.0-flash')
                ->startChat(history: [
                    Content::parse(part: $systemPrompt, role: Role::MODEL)
                ])
                ->sendMessage($prompt);

            return $result->text();
        } catch (\Exception $e) {
            Log::error('Erro ao chamar a API do Gemini: ' . $e->getMessage());
            return 'Desculpe, não consegui processar sua solicitação no momento. Tente novamente mais tarde.';
        }
    }
}
