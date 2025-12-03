<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Channel;
use App\Models\Team;
use Mockery;
use Gemini\Client;
use Gemini\Resources\GenerativeModel;

class ChatAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock do Gemini
        $this->mockGemini();
    }

    private function mockGemini()
    {
        // Mock do GeminiService
        // Como o controller injeta o GeminiService, podemos mockar a classe inteira
        // e substituir no container de injeção de dependência.
        
        $mockService = Mockery::mock(\App\Services\GeminiService::class);
        
        // Define o comportamento esperado dos métodos
        $mockService->shouldReceive('countTokens')->andReturn(100);
        $mockService->shouldReceive('generateSummary')->andReturn('Resumo do Dia Mockado');
        $mockService->shouldReceive('generateResponse')->andReturn('Resposta Final Mockada');
        
        // Substitui a instância real pela mockada no container do Laravel
        $this->app->instance(\App\Services\GeminiService::class, $mockService);
    }

    public function test_can_generate_summary()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $user->teams()->attach($team);
        $user->switchTeam($team);
        
        $channel = Channel::factory()->create(['team_id' => $team->id]);
        
        // Define datas fixas para evitar problemas de timezone/horário
        $targetDate = now()->subDays(1);
        $targetDateStr = $targetDate->format('Y-m-d');
        
        // Cria conversas ontem
        Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => '123',
            'message_body' => 'Teste mensagem 1',
            'direction' => 'incoming',
            'created_at' => $targetDate
        ]);

        $response = $this->actingAs($user)->postJson(route('analysis.summary'), [
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'include_today' => false
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['summary']);
            
        // Verifica se salvou no cache
        $this->assertDatabaseHas('daily_summaries', [
            'date' => $targetDateStr
        ]);
    }

    public function test_can_chat_with_context()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson(route('analysis.chat'), [
            'message' => 'O que aconteceu?',
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'history' => []
        ]);

        $response->assertStatus(200)
            ->assertJson(['response' => 'Resposta Final Mockada']);
    }

    public function test_includes_feedback_entries_in_summary()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $user->teams()->attach($team);
        $user->switchTeam($team);
        
        $channel = Channel::factory()->create(['team_id' => $team->id]);
        
        $targetDate = now()->subDays(1);
        $targetDateStr = $targetDate->format('Y-m-d');
        
        // Cria um feedback para ontem
        \App\Models\FeedbackEntry::factory()->create([
            'channel_id' => $channel->id,
            'created_at' => $targetDate,
            'tipo' => 'demanda',
            'descricao' => 'Teste de Reclamação Importante'
        ]);

        // Recria o mock para este teste específico para garantir que a expectativa seja atendida
        $mockService = Mockery::mock(\App\Services\GeminiService::class);
        $mockService->shouldReceive('countTokens')->andReturn(100);
        
        // Expectativa específica: deve receber o texto com os feedbacks
        $mockService->shouldReceive('generateSummary')
            ->with(Mockery::on(function ($arg) {
                return str_contains($arg, '=== REGISTROS OFICIAIS (FEEDBACKS) ===') &&
                       str_contains($arg, 'Teste de Reclamação Importante');
            }))
            ->andReturn('Resumo com Feedback');
            
        $mockService->shouldReceive('generateResponse')->andReturn('Resposta Final Mockada');
            
        $this->app->instance(\App\Services\GeminiService::class, $mockService);

        $response = $this->actingAs($user)->postJson(route('analysis.summary'), [
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('daily_summaries', [
            'summary' => 'Resumo com Feedback'
        ]);
    }

    public function test_redacts_pii_and_uses_correct_system_prompt()
    {
        $user = User::factory()->create();
        
        // Mock do GeminiService
        $mockService = Mockery::mock(\App\Services\GeminiService::class);
        $mockService->shouldReceive('countTokens')->andReturn(100);
        $mockService->shouldReceive('generateResponse')
            ->with(
                Mockery::any(), // User message
                Mockery::on(function ($context) {
                    // Verifica Instruções de Persona
                    $hasPersona = str_contains($context, 'ATENÇÃO: Você é um ANALISTA DE DADOS');
                    
                    // Verifica Redação de PII
                    $cpfRedacted = str_contains($context, '[CPF REDACTED]') && !str_contains($context, '123.456.789-00');
                    $emailRedacted = str_contains($context, '[EMAIL REDACTED]') && !str_contains($context, 'user@example.com');
                    
                    return $hasPersona && $cpfRedacted && $emailRedacted;
                })
            )
            ->andReturn('Análise Segura');
            
        $this->app->instance(\App\Services\GeminiService::class, $mockService);

        // Cria conversa com dados sensíveis
        $channel = Channel::factory()->create();
        Conversation::create([
            'channel_id' => $channel->id,
            'sender_identifier' => '123',
            'message_body' => 'Meu CPF é 123.456.789-00 e email user@example.com',
            'direction' => 'incoming',
            'created_at' => now()->subHour()
        ]);

        $response = $this->actingAs($user)->postJson(route('analysis.chat'), [
            'message' => 'Analise os dados',
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'history' => []
        ]);

        $response->assertStatus(200)
            ->assertJson(['response' => 'Análise Segura']);
    }
}
