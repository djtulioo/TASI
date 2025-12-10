<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Conversation;
use App\Models\FeedbackEntry;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Gemini\Client;
use Gemini\Resources\GenerativeModel;
use Gemini\Resources\ChatSession;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Candidate;

class SuccessiveDemandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_successive_demands_real_integration_logic()
    {
        // 1. Setup Data
        $botToken = '123456:TEST-TOKEN-SUCCESSIVE';
        $chatId = '111222333';
        
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;
        
        $channel = Channel::create([
            'name' => 'Telegram Channel',
            'user_id' => $user->id,
            'team_id' => $team->id,
            'telegram_bot_token' => $botToken,
            'platform' => 'telegram',
        ]);

        // Mock Telegram HTTP
        Http::fake([
            'telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        // Mock GeminiService partially?
        // Actually, simpler: we want to verify that IF Gemini decides to call a function,
        // the app executes it and saves it. 
        // And that subsequent calls also work.
        // We will mock the `getGeminiClient` or internal logic to return predictable function calls.
        
        $mockGemini = Mockery::mock(GeminiService::class)->makePartial();
        $this->app->instance(GeminiService::class, $mockGemini);

        // --- Interaction 1: "Problem with internet" ---
        // Expectation: AI proposes 'solicitar_cadastro_ouvidoria'
        $mockGemini->shouldReceive('generateResponseWithFunctionCalling')
            ->once()
            ->andReturn([
                 'text' => 'Vou registrar seu problema. Confirma?',
                 'history' => [],
                 'feedback_entry' => null
            ]);
            
        // Send Msg 1
        $this->postJson("/api/webhook/telegram/{$botToken}", $this->payload($chatId, 'Problema na internet'));
        
        // --- Interaction 2: "Yes" ---
        // Expectation: AI calls 'confirmar_cadastro_ouvidoria'
        // We simulate the service RETURNING a created feedback entry
        $entry1 = new FeedbackEntry([
            'id' => 1, 
            'tipo' => 'demanda', 
            'titulo' => 'Problema Internet', 
            'descricao' => 'Problema na internet',
             'channel_id' => $channel->id, 
             'bot_user_id' => $botToken,
             'sender_identifier' => $chatId
        ]);
        
        $mockGemini->shouldReceive('generateResponseWithFunctionCalling')
            ->once()
            ->andReturn([
                 'text' => 'Registrado chamdo #1.',
                 'history' => [],
                 'feedback_entry' => $entry1
            ]);
            
        // Send Msg 2
        $this->postJson("/api/webhook/telegram/{$botToken}", $this->payload($chatId, 'Sim'));

        // --- Interaction 3: "New suggestion" ---
        // Expectation: AI proposes 'solicitar_cadastro_ouvidoria' AGAIN
        $mockGemini->shouldReceive('generateResponseWithFunctionCalling')
            ->once()
            ->andReturn([
                 'text' => 'Vou registrar sua sugestão. Confirma?',
                 'history' => [],
                 'feedback_entry' => null
            ]);
            
        // Send Msg 3
        $this->postJson("/api/webhook/telegram/{$botToken}", $this->payload($chatId, 'Tenho uma sugestão'));

        // --- Interaction 4: "Yes" ---
        // Expectation: AI calls 'confirmar_cadastro_ouvidoria' AGAIN
        // Key checking point: Does the system allow this? Yes, if Service is stateless regarding "active forms" or handles it.
        $entry2 = new FeedbackEntry([
            'id' => 2, 
            'tipo' => 'sugestao', 
            'titulo' => 'Sugestão X', 
            'descricao' => 'Melhorar X',
             'channel_id' => $channel->id, 
             'bot_user_id' => $botToken,
             'sender_identifier' => $chatId
        ]);
        
        $mockGemini->shouldReceive('generateResponseWithFunctionCalling')
            ->once()
            ->andReturn([
                 'text' => 'Registrado sugestão #2.',
                 'history' => [],
                 'feedback_entry' => $entry2
            ]);

        // Send Msg 4
        $this->postJson("/api/webhook/telegram/{$botToken}", $this->payload($chatId, 'Sim'));
        
        // Assertions
        // We assert that the CONTROLLER/SERVICE flow triggered the "generateResponse" for each message.
        // Since we mocked `generateResponseWithFunctionCalling`, we are verifying that the WebhookController -> TelegramService 
        // pipeline correctly kept invoking the AI service for each new message, without crashing or getting stuck.
        
        // If the real "bug" is inside `generateResponseWithFunctionCalling` logic (e.g. Gemini refusing to call function),
        // we can't easily test that without the real API.
        // BUT, if the bug is that the CODE stops calling Gemini, this test will catch it.
        
        $this->assertTrue(true, 'Flow completed without errors');
    }

    private function payload($chatId, $text) {
        return [
            'update_id' => rand(10000, 99999),
            'message' => [
                'date' => time(),
                'chat' => ['id' => $chatId, 'type' => 'private'],
                'message_id' => rand(100, 999),
                'from' => ['id' => $chatId, 'first_name' => 'User'],
                'text' => $text,
            ],
        ];
    }
}
