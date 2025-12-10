<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Conversation;
use App\Models\FeedbackEntry;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_conversations_from_different_channel_with_same_bot_identity()
    {
        // 1. Criar Usuário e Time A
        $userA = User::factory()->create();
        $teamA = Team::factory()->create([
            'user_id' => $userA->id,
            'personal_team' => true,
        ]);
        $userA->switchTeam($teamA);

        // 2. Criar Canal A vinculado ao Time A (Bot A - Whatsapp 12345)
        $channelA = Channel::factory()->create([
            'team_id' => $teamA->id,
            'name' => 'Canal A',
            'type' => 'whatsapp',
            'phone_number_id' => '123456789',
        ]);
        $teamA->last_selected_channel_id = $channelA->id;
        $teamA->save();

        // 3. Criar conversa no Canal A
        $conversationA = Conversation::create([
            'channel_id' => $channelA->id,
            'bot_user_id' => '123456789', // Simulando o comportamento do Controller
            'sender_identifier' => '5511999999999',
            'message_body' => 'Mensagem Original',
            'direction' => 'incoming',
        ]);

        // 4. Criar Feedback no Canal A
        FeedbackEntry::create([
            'channel_id' => $channelA->id,
            'bot_user_id' => '123456789',
            'conversation_id' => $conversationA->id,
            'tipo' => 'opiniao', // 'elogio' não existe no enum
            'descricao' => 'Bom atendimento', // 'conteudo' não existe, é 'descricao'
            'status' => 'pendente',
        ]);

        // 5. Criar Usuário e Time B (Outro contexto)
        $userB = User::factory()->create();
        $teamB = Team::factory()->create([
            'user_id' => $userB->id,
            'personal_team' => true,
        ]);
        $userB->switchTeam($teamB);

        // 6. Criar Canal B vinculado ao Time B (Bot A - MESMO Whatsapp 12345)
        $channelB = Channel::factory()->create([
            'team_id' => $teamB->id,
            'name' => 'Canal B',
            'type' => 'whatsapp',
            'phone_number_id' => '123456789', // MESMO ID
        ]);
        $teamB->last_selected_channel_id = $channelB->id;
        $teamB->save();

        // 7. Autenticar como Usuário B
        $this->actingAs($userB);

        // 8. Verificar se consegue ver a conversa do Canal A via API de chat
        $response = $this->get('/chat');
        $response->assertStatus(200);
        $response->assertSee('Mensagem Original'); // Deve aparecer no histórico

        // 9. Verificar API de mensagens específicas
        $response = $this->get(route('chat.messages', '5511999999999')); // Usando helper route() para garantir
        $response->assertStatus(200);
        $response->assertJsonFragment(['text' => 'Mensagem Original']);

        // 10. Verificar Feedback
        $response = $this->get('/feedback-entries');
        $response->assertStatus(200);
        $response->assertSee('Bom atendimento');
    }

    public function test_cannot_view_conversations_from_different_bot_identity()
    {
        // 1. Setup similar, mas com phone_number_id DIFERENTE
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id]);
        $channelA = Channel::factory()->create([
            'team_id' => $teamA->id,
            'phone_number_id' => 'BOT_A_ID',
        ]);
        
        Conversation::create([
            'channel_id' => $channelA->id,
            'bot_user_id' => 'BOT_A_ID',
            'sender_identifier' => 'client_1',
            'message_body' => 'Segredo do Bot A',
            'direction' => 'incoming',
        ]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);
        $channelB = Channel::factory()->create([
            'team_id' => $teamB->id,
            'phone_number_id' => 'BOT_B_ID', // IDENTIDADE DIFERENTE
        ]);
        $teamB->last_selected_channel_id = $channelB->id;
        $teamB->save();

        $this->actingAs($userB);

        $response = $this->get('/chat');
        $response->assertDontSee('Segredo do Bot A');

        $response = $this->get(route('chat.messages', 'client_1'));
        $response->assertJsonCount(0);
    }
}
