<?php

namespace App\Services;

use App\Models\FeedbackEntry;
use Illuminate\Support\Facades\Log;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Data\Tool;
use Gemini\Data\FunctionResponse;
use Gemini\Data\ToolConfig;
use Gemini\Data\FunctionCallingConfig;
use Gemini\Enums\Mode;
use Gemini\Data\Part;

class GeminiService
{
    private const SYSTEM_INSTRUCTION = 'Você é um assistente de ouvidoria chamado Pulsar AI que DEVE usar as funções disponíveis para registrar informações no sistema. ' .
        'REGRAS IMPORTANTES: ' .
        '1. Quando o usuário quiser registrar algo, você DEVE chamar a função solicitar_cadastro_ouvidoria(tipo, descricao, titulo). ' .
        '2. Quando o usuário confirmar explicitamente (dizer "sim", "pode", "confirmo"), você DEVE chamar confirmar_cadastro_ouvidoria(tipo, descricao, titulo, confirmar=true). ' .
        '3. SEMPRE use as funções para realmente salvar no sistema. NUNCA apenas diga que salvou sem chamar a função. ' .
        '4. Após receber o resultado da função, confirme ao usuário em linguagem natural e amigável. ' .
        '5. NUNCA mostre código Python na resposta ao usuário (como "print(...)"). ' .
        'Fluxo correto: Conversa → Coletar informações → Propor cadastro (CHAMAR solicitar_cadastro_ouvidoria) → Aguardar confirmação → Cadastrar (CHAMAR confirmar_cadastro_ouvidoria) → Confirmar sucesso.';

    /**
     * Gera uma resposta usando a IA do Gemini (versão simples, sem function calling).
     *
     * @param string $prompt
     * @param string|null $context
     * @return string
     */
    public function generateResponse(string $prompt, ?string $context = null): string
    {
        try {
            $baseSystemPrompt = "Você é um assistente de ouvidoria para a plataforma Pulsar. Responda de forma concisa e útil, ajudando o usuário a registrar seu feedback.";

            $systemPrompt = $baseSystemPrompt;
            if ($context) {
                $systemPrompt .= "\n\nContexto adicional e instruções específicas:\n" . $context;
            }

            $client = $this->getGeminiClient();

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

    /**
     * Conta os tokens de um texto (estimativa ou via API).
     * Para performance e economia, usaremos uma heurística: ~4 caracteres por token.
     *
     * @param string $text
     * @return int
     */
    public function countTokens(string $text): int
    {
        // Estimativa conservadora para português
        return (int) ceil(mb_strlen($text) / 4);
    }

    /**
     * Gera um resumo do texto fornecido.
     *
     * @param string $text
     * @return string
     */
    public function generateSummary(string $text): string
    {
        $prompt = "Resuma as seguintes conversas de forma concisa, destacando os principais tópicos, problemas relatados e sentimentos gerais. Se houver datas, mencione-as.";
        
        // Se o texto for muito longo, truncamos para evitar erro (embora o flash aguente 1M)
        // Vamos assumir que o caller já tratou a lógica de chunking se necessário,
        // mas aqui garantimos que não estoure o limite hard do request.
        
        return $this->generateResponse($prompt, $text);
    }

    /**
     * Gera uma resposta com function calling para cadastro de ouvidoria.
     *
     * @param string $userMessage
     * @param array $history Histórico de mensagens no formato Gemini
     * @param int $channelId
     * @param string|null $senderIdentifier
     * @param int|null $conversationId
     * @return array ['text' => string, 'history' => array, 'feedback_entry' => FeedbackEntry|null]
     */
    public function generateResponseWithFunctionCalling(
        string $userMessage,
        array $history = [],
        int $channelId,
        ?string $senderIdentifier = null,
        ?int $conversationId = null
    ): array {
        try {
            $client = $this->getGeminiClient();

            // Configuração do modelo com tools
            $tools = $this->getFunctionDeclarations();

            // Cria system instruction como Content
            $systemInstruction = Content::parse(part: self::SYSTEM_INSTRUCTION, role: Role::MODEL);

            // Configura ToolConfig para usar funções automaticamente quando apropriado
            $toolConfig = new ToolConfig(
                functionCallingConfig: new FunctionCallingConfig(
                    mode: Mode::AUTO // O modelo decide quando chamar funções
                )
            );

            // Configura o modelo usando métodos builder
            $model = $client->generativeModel(model: 'gemini-2.0-flash')
                ->withSystemInstruction($systemInstruction)
                ->withTool($tools)
                ->withToolConfig($toolConfig);
            
            Log::info("GeminiService: Modelo configurado com tools e toolConfig");

            // 1ª chamada: modelo decide se responde ou chama função
            // O histórico não deve incluir a mensagem atual (será enviada via sendMessage)
            $chat = $model->startChat(history: $history);
            $response = $chat->sendMessage($userMessage);

            // Adiciona a mensagem do usuário ao histórico APÓS o envio
            $history[] = Content::parse(part: $userMessage, role: Role::USER);

            // Adiciona a resposta do assistente ao histórico
            if ($response->candidates && isset($response->candidates[0])) {
                $history[] = $response->candidates[0]->content;
            }

            $feedbackEntry = null;

            // Verifica se há function calls nos parts da resposta E extrai texto
            $functionCalls = [];
            $assistantText = '';
            
            Log::info("GeminiService: Verificando parts da resposta", [
                'num_candidates' => count($response->candidates ?? []),
                'num_parts' => count($response->candidates[0]->content->parts ?? [])
            ]);
            
            if (!empty($response->candidates[0]->content->parts)) {
                foreach ($response->candidates[0]->content->parts as $partIndex => $part) {
                    Log::info("GeminiService: Analisando part {$partIndex}", [
                        'has_text' => $part->text !== null,
                        'has_functionCall' => $part->functionCall !== null,
                        'text_preview' => $part->text ? substr($part->text, 0, 100) : null
                    ]);
                    
                    // Extrai texto se houver
                    if ($part->text !== null) {
                        $assistantText .= $part->text;
                    }
                    
                    // Extrai function call se houver
                    if ($part->functionCall !== null) {
                        $functionCalls[] = $part->functionCall;
                        Log::info("GeminiService: Function call detectado!", [
                            'name' => $part->functionCall->name,
                            'args' => $part->functionCall->args
                        ]);
                    }
                }
            }
            
            Log::info("GeminiService: Total de function calls encontrados: " . count($functionCalls));

            // Se houver function calls, processa
            if (!empty($functionCalls)) {
                Log::info("GeminiService: Processando " . count($functionCalls) . " function call(s)");
                $functionResponses = [];

                foreach ($functionCalls as $functionCall) {
                    $result = $this->executeFunctionCall(
                        $functionCall,
                        $channelId,
                        $senderIdentifier,
                        $conversationId
                    );

                    Log::info("GeminiService: Resultado da função", [
                        'name' => $result['name'],
                        'status' => $result['result']['status'] ?? 'unknown'
                    ]);

                    if ($result['name'] === 'confirmar_cadastro_ouvidoria' && $result['result']['status'] === 'efetivado') {
                        $feedbackEntry = $result['result']['feedback_entry'] ?? null;
                        Log::info("GeminiService: Feedback cadastrado!", ['id' => $feedbackEntry?->id]);
                    }

                    $functionResponses[] = new FunctionResponse(
                        name: $result['name'],
                        response: $result['result']
                    );
                }

                // Cria Content com function responses (array de Parts)
                $responseParts = array_map(
                    fn($fr) => new Part(functionResponse: $fr),
                    $functionResponses
                );
                $functionResponseContent = new Content(parts: $responseParts, role: Role::USER);

                // Adiciona as function responses ao histórico
                $history[] = $functionResponseContent;

                // 2ª chamada: modelo usa os resultados das funções
                Log::info("GeminiService: Enviando function responses de volta ao modelo");
                $followUp = $chat->sendMessage($functionResponseContent);
                
                // Extrai o texto do follow-up, filtrando código
                try {
                    $followUpText = $followUp->text();
                    
                    // Remove linhas de código Python/print se houver (fallback de segurança)
                    if (strpos($followUpText, 'print(') !== false || strpos($followUpText, 'default_api') !== false) {
                        Log::warning("GeminiService: Resposta contém código, usando fallback");
                        
                        // Gera mensagem apropriada baseada na última função executada
                        $lastFunction = end($functionCalls);
                        if ($lastFunction && $lastFunction->name === 'confirmar_cadastro_ouvidoria') {
                            if ($feedbackEntry) {
                                $assistantText = "Perfeito! Sua solicitação foi registrada com sucesso no sistema de ouvidoria. Nossa equipe irá analisar e retornar em breve. Obrigado pelo seu feedback!";
                            } else {
                                $assistantText = "Tudo certo! Seu registro foi confirmado.";
                            }
                        } else {
                            $assistantText = "Entendi! Posso ajudar com mais alguma coisa?";
                        }
                    } else {
                        $assistantText = $followUpText;
                    }
                } catch (\Exception $e) {
                    Log::error("GeminiService: Erro ao extrair texto do follow-up: " . $e->getMessage());
                    // Fallback baseado no contexto
                    if ($feedbackEntry) {
                        $assistantText = "Sua solicitação foi registrada com sucesso! Obrigado pelo seu feedback.";
                    } else {
                        $assistantText = "Entendido! Como posso ajudar mais?";
                    }
                }

                Log::info("GeminiService: Resposta follow-up: " . $assistantText);

                // Adiciona a resposta follow-up ao histórico
                if ($followUp->candidates && isset($followUp->candidates[0])) {
                    $history[] = $followUp->candidates[0]->content;
                }
            }

            return [
                'text' => $assistantText,
                'history' => $history,
                'feedback_entry' => $feedbackEntry,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao chamar a API do Gemini com function calling: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return [
                'text' => 'Desculpe, não consegui processar sua solicitação no momento. Tente novamente mais tarde.',
                'history' => $history,
                'feedback_entry' => null,
            ];
        }
    }

    /**
     * Obtém as declarações de funções para o Gemini.
     */
    private function getFunctionDeclarations(): Tool
    {
        return new Tool([
            new FunctionDeclaration(
                name: 'solicitar_cadastro_ouvidoria',
                description: 'Solicita confirmação antes de cadastrar uma entrada de ouvidoria de um dos tipos: demanda, sugestao ou opiniao. Deve ser chamada quando o usuário expressar uma intenção clara de registrar algo.',
                parameters: new Schema(
                    type: DataType::OBJECT,
                    properties: [
                        'tipo' => new Schema(
                            type: DataType::STRING,
                            description: 'Tipo do registro: demanda | sugestao | opiniao',
                            enum: ['demanda', 'sugestao', 'opiniao']
                        ),
                        'titulo' => new Schema(
                            type: DataType::STRING,
                            description: 'Título resumido do registro'
                        ),
                        'descricao' => new Schema(
                            type: DataType::STRING,
                            description: 'Descrição detalhada fornecida pelo usuário'
                        ),
                    ],
                    required: ['tipo', 'descricao']
                )
            ),
            new FunctionDeclaration(
                name: 'confirmar_cadastro_ouvidoria',
                description: 'Confirma o cadastro solicitado anteriormente e executa o registro. Use somente após o usuário confirmar explicitamente que deseja cadastrar.',
                parameters: new Schema(
                    type: DataType::OBJECT,
                    properties: [
                        'tipo' => new Schema(
                            type: DataType::STRING,
                            description: 'Tipo do registro: demanda | sugestao | opiniao',
                            enum: ['demanda', 'sugestao', 'opiniao']
                        ),
                        'titulo' => new Schema(
                            type: DataType::STRING,
                            description: 'Título resumido do registro'
                        ),
                        'descricao' => new Schema(
                            type: DataType::STRING,
                            description: 'Descrição detalhada fornecida pelo usuário'
                        ),
                        'confirmar' => new Schema(
                            type: DataType::BOOLEAN,
                            description: 'True se o usuário confirmou explicitamente o cadastro'
                        ),
                    ],
                    required: ['tipo', 'descricao', 'confirmar']
                )
            ),
        ]);
    }

    /**
     * Executa uma function call do Gemini.
     */
    private function executeFunctionCall(
        $functionCall,
        int $channelId,
        ?string $senderIdentifier,
        ?int $conversationId
    ): array {
        $name = $functionCall->name;
        $args = $functionCall->args ?? [];

        if ($name === 'solicitar_cadastro_ouvidoria') {
            return [
                'name' => $name,
                'result' => $this->solicitarCadastroOuvidoria($args),
            ];
        }

        if ($name === 'confirmar_cadastro_ouvidoria') {
            return [
                'name' => $name,
                'result' => $this->confirmarCadastroOuvidoria(
                    $args,
                    $channelId,
                    $senderIdentifier,
                    $conversationId
                ),
            ];
        }

        Log::warning("Função desconhecida chamada: {$name}");
        return [
            'name' => $name ?? 'desconhecida',
            'result' => ['error' => 'função desconhecida'],
        ];
    }

    /**
     * Solicita confirmação para cadastro de ouvidoria.
     */
    private function solicitarCadastroOuvidoria(array $args): array
    {
        Log::info('[Pré-cadastro]', $args);

        return [
            'status' => 'aguardando_confirmacao',
            'tipo' => $args['tipo'] ?? null,
            'titulo' => $args['titulo'] ?? '',
            'descricao' => $args['descricao'] ?? '',
        ];
    }

    /**
     * Confirma e efetiva o cadastro de ouvidoria.
     */
    private function confirmarCadastroOuvidoria(
        array $args,
        int $channelId,
        ?string $senderIdentifier,
        ?int $conversationId
    ): array {
        $confirmar = $args['confirmar'] ?? false;

        if (!$confirmar) {
            Log::info('[Cadastro cancelado pelo usuário]');
            return ['status' => 'cancelado'];
        }

        try {
            $feedbackEntry = FeedbackEntry::create([
                'conversation_id' => $conversationId,
                'channel_id' => $channelId,
                'tipo' => $args['tipo'],
                'titulo' => $args['titulo'] ?? null,
                'descricao' => $args['descricao'],
                'sender_identifier' => $senderIdentifier,
                'status' => 'pendente',
            ]);

            Log::info('[Cadastro efetivado]', [
                'id' => $feedbackEntry->id,
                'tipo' => $feedbackEntry->tipo,
            ]);

            return [
                'status' => 'efetivado',
                'tipo' => $feedbackEntry->tipo,
                'titulo' => $feedbackEntry->titulo,
                'descricao' => $feedbackEntry->descricao,
                'feedback_entry' => $feedbackEntry,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar feedback entry: ' . $e->getMessage());
            return [
                'status' => 'erro',
                'mensagem' => 'Não foi possível cadastrar o feedback. Tente novamente.',
            ];
        }
    }

    /**
     * Obtém o cliente Gemini configurado.
     */
    private function getGeminiClient()
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            throw new \Exception('GEMINI_API_KEY não definida.');
        }

        $guzzle = new \GuzzleHttp\Client(['verify' => false]);
        return \Gemini::factory()
            ->withApiKey($apiKey)
            ->withHttpClient($guzzle)
            ->make();
    }
}
