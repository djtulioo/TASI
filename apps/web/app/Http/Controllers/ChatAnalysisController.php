<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatAnalysisController extends Controller
{
    protected $geminiService;

    public function __construct(\App\Services\GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Gera ou recupera o resumo das conversas no período.
     */
    public function summary(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'include_today' => 'boolean'
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
        $includeToday = $request->boolean('include_today', false);

        // Separa "passado" (até ontem) de "hoje"
        $yesterday = now()->subDay()->endOfDay();
        $pastEndDate = $endDate->min($yesterday);
        
        $summaries = [];
        
        // 1. Processa dias passados (Cacheável)
        if ($startDate->lte($pastEndDate)) {
            $period = \Carbon\CarbonPeriod::create($startDate, $pastEndDate);
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                
                // Tenta buscar do cache
                $dailySummary = \DB::table('daily_summaries')->where('date', $dateStr)->first();
                
                if ($dailySummary) {
                    $summaries[] = "Dia $dateStr: " . $dailySummary->summary;
                } else {
                    // 1. Busca Registros Oficiais (Prioridade)
                    $feedbacks = \App\Models\FeedbackEntry::whereDate('created_at', $dateStr)->get();
                    
                    if ($feedbacks->isNotEmpty()) {
                         // Found! Continue.
                    } else {
                         // throw new \Exception("No feedbacks found for date: " . $dateStr);
                    }
                    
                    // 2. Busca Conversas (Contexto)
                    $conversations = \App\Models\Conversation::whereDate('created_at', $dateStr)->get();
                    
                    if ($conversations->isEmpty() && $feedbacks->isEmpty()) {
                        continue;
                    }
                    
                    $text = "";
                    
                    // Formata Feedbacks (Structured First)
                    if ($feedbacks->isNotEmpty()) {
                        $text .= "=== REGISTROS OFICIAIS (FEEDBACKS) ===\n";
                        $text .= $this->formatFeedbacks($feedbacks) . "\n\n";
                    }
                    
                    // Formata Conversas
                    if ($conversations->isNotEmpty()) {
                        $text .= "=== HISTÓRICO DE CONVERSAS ===\n";
                        $text .= $this->formatConversations($conversations);
                    }
                    
                    $summaryText = $this->geminiService->generateSummary($text);
                    $tokenCount = $this->geminiService->countTokens($text);
                    
                    \DB::table('daily_summaries')->insert([
                        'date' => $dateStr,
                        'summary' => $summaryText,
                        'token_count' => $tokenCount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $summaries[] = "Dia $dateStr: " . $summaryText;
                }
            }
        }

        // 2. Processa hoje (On-the-fly) se solicitado ou se o range incluir hoje e for update manual
        if ($includeToday && $endDate->isToday()) {
            $todayStr = now()->format('Y-m-d');
            $conversations = \App\Models\Conversation::whereDate('created_at', $todayStr)->get();
            
            if ($conversations->isNotEmpty()) {
                $text = $this->formatConversations($conversations);
                $todaySummary = $this->geminiService->generateSummary($text);
                $summaries[] = "HOJE ($todayStr) [Parcial]: " . $todaySummary;
            }
        }

        // 3. Gera Resumo Mestre
        if (empty($summaries)) {
            return response()->json(['summary' => 'Nenhuma conversa encontrada no período.']);
        }

        $masterText = implode("\n\n", $summaries);
        $finalSummary = $this->geminiService->generateResponse(
            "Consolide os seguintes resumos diários em um único relatório executivo do período. Destaque tendências.",
            $masterText
        );

        return response()->json(['summary' => $finalSummary]);
    }

    /**
     * Chat com contexto das conversas (RAG Simplificado).
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'history' => 'array'
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

        // Busca Registros Oficiais (Prioridade Máxima)
        $feedbacks = \App\Models\FeedbackEntry::whereBetween('created_at', [$startDate, $endDate])->get();
        
        // Busca conversas
        $query = \App\Models\Conversation::whereBetween('created_at', [$startDate, $endDate]);
        
        // Contagem rápida de volume (estimativa)
        $count = $query->count();
        
        $context = "";
        
        // Adiciona Feedbacks primeiro (Structured First)
        if ($feedbacks->isNotEmpty()) {
            $context .= "=== REGISTROS OFICIAIS (FEEDBACKS) - ALTA PRIORIDADE ===\n";
            $context .= $this->formatFeedbacks($feedbacks) . "\n\n";
        }
        
        $context .= "=== HISTÓRICO DE CONVERSAS (CONTEXTO) ===\n";
        
        // Estratégia Híbrida para Conversas
        if ($count < 1000) {
            // Volume baixo: manda tudo (Long Context)
            $conversations = $query->get();
            $context .= $this->formatConversations($conversations);
        } else {
            // Volume alto: Context Pruning (Recente + Relevante)
            
            // 1. Recentes (últimas 50)
            $recent = $query->latest()->take(50)->get()->reverse();
            
            // 2. Relevantes (Keyword Search simples)
            // Extrai palavras-chave da pergunta (removendo stopwords básicas)
            $keywords = array_filter(explode(' ', $request->message), fn($w) => strlen($w) > 3);
            
            $relevant = collect();
            if (!empty($keywords)) {
                $relevantQuery = \App\Models\Conversation::whereBetween('created_at', [$startDate, $endDate])
                    ->where(function($q) use ($keywords) {
                        foreach ($keywords as $word) {
                            $q->orWhere('message_body', 'like', "%$word%");
                        }
                    })
                    ->limit(50)
                    ->get();
                $relevant = $relevantQuery;
            }
            
            $merged = $recent->merge($relevant)->unique('id')->sortBy('created_at');
            $context .= $this->formatConversations($merged);
            $context .= "\n\n[NOTA: Devido ao alto volume, este contexto é uma seleção das mensagens mais recentes e relevantes à sua pergunta.]";
        }

        $response = $this->geminiService->generateResponse(
            $request->message,
            "ATENÇÃO: Você é um ANALISTA DE DADOS e ADMINISTRADOR do sistema. NÃO atue como o atendente do chat.\n" .
            "Sua função é ler os logs de conversas abaixo e extrair insights, responder dúvidas sobre o que aconteceu, ou resumir fatos.\n" .
            "NUNCA responda como se estivesse falando com o usuário final das conversas. Você está falando com o CHEFE da ouvidoria.\n" .
            "Os dados abaixo foram anonimizados para proteção.\n\n" .
            "CONTEXTO (LOGS):\n" . $context
        );

        return response()->json(['response' => $response]);
    }

    private function formatConversations($conversations)
    {
        return $conversations->map(function ($c) {
            $direction = $c->direction === 'incoming' ? 'Usuário' : 'Atendente';
            $body = $this->redactPII($c->message_body);
            return "[{$c->created_at->format('d/m H:i')}] $direction: {$body}";
        })->implode("\n");
    }

    private function formatFeedbacks($feedbacks)
    {
        return $feedbacks->map(function ($f) {
            $desc = $this->redactPII($f->descricao);
            return "[{$f->created_at->format('d/m H:i')}] TIPO: {$f->tipo} | STATUS: {$f->status}\n" .
                   "DESCRIÇÃO: {$desc}";
        })->implode("\n---\n");
    }

    /**
     * Redacts PII (Personally Identifiable Information) from text.
     */
    private function redactPII($text)
    {
        // CPF (Simple regex for 11 digits with or without separators)
        $text = preg_replace('/(\d{3})\.?(\d{3})\.?(\d{3})-?(\d{2})/', '[CPF REDACTED]', $text);

        // Email
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL REDACTED]', $text);

        // Phone (Simple regex for 10-11 digits)
        $text = preg_replace('/\(?\d{2}\)?\s?\d{4,5}-?\d{4}/', '[PHONE REDACTED]', $text);

        return $text;
    }
}
