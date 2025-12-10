<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Conversation;
use App\Models\User;
use App\Services\GeminiService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_webhook_stores_bot_user_id()
    {
        // 1. Arrange
        $botToken = '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';
        $chatId = '987654321';
        $messageText = 'Hello World';

        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;
        
        $channel = Channel::create([
            'name' => 'Telegram Channel',
            'user_id' => $user->id,
            'team_id' => $team->id,
            'telegram_bot_token' => $botToken,
            'platform' => 'telegram',
        ]);

        // Mock GeminiService to avoid external API calls
        $mockGeminiService = Mockery::mock(GeminiService::class);
        $mockGeminiService->shouldReceive('generateResponseWithFunctionCalling')
            ->once()
            ->withArgs(function ($userMessage, $history, $channelId, $senderIdentifier, $conversationId, $botUserId) use ($messageText, $channel, $chatId, $botToken) {
                return $userMessage === $messageText &&
                       $channelId === $channel->id &&
                       $senderIdentifier === $chatId &&
                       $botUserId === $botToken;
            })
            ->andReturn([
                'text' => 'AI Response',
                'history' => [],
                'feedback_entry' => null,
            ]);

        // Mock Http for Telegram sendMessage calls
        Http::fake([
            'telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        // Swap the GeminiService in the container
        $this->app->instance(GeminiService::class, $mockGeminiService);

        // 2. Act
        $payload = [
            'update_id' => 10000,
            'message' => [
                'date' => 1441645532,
                'chat' => [
                    'last_name' => 'Test',
                    'id' => $chatId,
                    'first_name' => 'User',
                    'username' => 'TestUser',
                ],
                'message_id' => 1365,
                'from' => [
                    'last_name' => 'Test',
                    'id' => $chatId,
                    'first_name' => 'User',
                    'username' => 'TestUser',
                ],
                'text' => $messageText,
            ],
        ];

        $response = $this->postJson("/api/webhook/telegram/{$botToken}", $payload);

        // 3. Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('conversations', [
            'channel_id' => $channel->id,
            'bot_user_id' => $botToken,
            'sender_identifier' => $chatId,
            'message_body' => $messageText,
            'direction' => 'incoming',
        ]);

        $this->assertDatabaseHas('conversations', [
            'channel_id' => $channel->id,
            'bot_user_id' => $botToken,
            'sender_identifier' => $chatId, // Outgoing message also uses chatId as sender identifier for grouping usually, or 'bot' depending on implementation. Let's check logic.
            // In TelegramService: 'sender_identifier' => $senderId (which is chatId)
            'message_body' => 'AI Response',
            'direction' => 'outgoing',
        ]);
    }
}
