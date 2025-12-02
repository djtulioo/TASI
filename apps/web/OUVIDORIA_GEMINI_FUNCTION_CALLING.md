# Sistema de Ouvidoria com Gemini AI - Function Calling

## Visão Geral

Este sistema implementa um assistente de ouvidoria inteligente usando Gemini AI com Function Calling. O assistente pode conversar com usuários e automaticamente identificar quando eles desejam registrar:

- **Demandas**: Solicitações ou necessidades do usuário
- **Sugestões**: Ideias e propostas de melhoria
- **Opiniões**: Comentários e feedbacks gerais

## Arquitetura

### Componentes Principais

1. **FeedbackEntry Model** (`app/Models/FeedbackEntry.php`)
   - Armazena as entradas de ouvidoria no banco de dados
   - Campos: tipo, titulo, descricao, status, sender_identifier

2. **GeminiService** (`app/Services/GeminiService.php`)
   - Gerencia a comunicação com a API do Gemini
   - Implementa Function Calling para capturar intenções do usuário
   - Duas funções principais:
     - `solicitar_cadastro_ouvidoria`: Propõe um cadastro para confirmação
     - `confirmar_cadastro_ouvidoria`: Efetiva o cadastro após confirmação

3. **FeedbackEntryController** (`app/Http/Controllers/FeedbackEntryController.php`)
   - Gerencia CRUD de feedback entries
   - Método `processMessage`: Processa mensagens com IA

4. **Migration** (`database/migrations/2025_12_02_193009_create_feedback_entries_table.php`)
   - Tabela feedback_entries com campos necessários

## Como Funciona

### Fluxo de Conversação

1. **Usuário envia mensagem**
   ```
   POST /api/feedback/process-message
   {
     "message": "Gostaria de sugerir melhorias no sistema",
     "channel_id": 1,
     "sender_identifier": "user123"
   }
   ```

2. **Gemini AI analisa a mensagem**
   - Se detectar intenção de registro, chama `solicitar_cadastro_ouvidoria`
   - Retorna uma proposta de cadastro para o usuário confirmar

3. **Usuário confirma**
   ```
   Usuário: "Sim, pode cadastrar"
   ```

4. **Gemini AI confirma e salva**
   - Chama `confirmar_cadastro_ouvidoria` com `confirmar: true`
   - Cria registro no banco de dados
   - Retorna confirmação ao usuário

### Exemplo de Uso via API

```javascript
// 1ª Mensagem - Exploração
const response1 = await fetch('/api/feedback/process-message', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    message: "Tenho uma sugestão para melhorar o aplicativo",
    channel_id: 1,
    sender_identifier: "user@example.com",
    history: []
  })
});

const result1 = await response1.json();
// result1.response: "Ótimo! Gostaria de me contar sua sugestão?"

// 2ª Mensagem - Descrição
const response2 = await fetch('/api/feedback/process-message', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    message: "Seria legal ter modo escuro no app",
    channel_id: 1,
    sender_identifier: "user@example.com",
    history: result1.history
  })
});

const result2 = await response2.json();
// result2.response: "Entendi! Você gostaria de registrar isso como uma sugestão?"
// O Gemini chamou solicitar_cadastro_ouvidoria internamente

// 3ª Mensagem - Confirmação
const response3 = await fetch('/api/feedback/process-message', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    message: "Sim, pode cadastrar",
    channel_id: 1,
    sender_identifier: "user@example.com",
    history: result2.history
  })
});

const result3 = await response3.json();
// result3.feedback_entry: { id: 1, tipo: "sugestao", descricao: "...", ... }
// result3.response: "Pronto! Sua sugestão foi cadastrada com sucesso."
```

## Rotas Disponíveis

### API Routes

```php
// Processar mensagem com IA
POST /api/feedback/process-message
Body: {
  message: string,
  channel_id: int,
  sender_identifier?: string,
  conversation_id?: int,
  history?: array
}
```

### Web Routes (Autenticadas)

```php
// Listar feedback entries
GET /feedback-entries

// Ver feedback entry específico
GET /feedback-entries/{id}

// Atualizar status
PUT /feedback-entries/{id}

// Excluir feedback entry
DELETE /feedback-entries/{id}
```

## Tipos de Feedback

### Demanda
Solicitações e necessidades do usuário que requerem ação ou resposta.

**Exemplo:**
- "Preciso de ajuda com meu cadastro"
- "Quero solicitar acesso à nova funcionalidade"

### Sugestão
Ideias e propostas de melhoria para o sistema ou serviço.

**Exemplo:**
- "Seria legal ter modo escuro"
- "Poderiam adicionar filtros de busca"

### Opinião
Comentários gerais, feedbacks e experiências do usuário.

**Exemplo:**
- "Adorei a nova interface"
- "O aplicativo está mais rápido"

## Status dos Feedbacks

- `pendente`: Recém criado, aguardando análise
- `em_analise`: Sendo avaliado pela equipe
- `resolvido`: Finalizado/implementado
- `cancelado`: Descartado ou não será implementado

## Integrações

O sistema está integrado com:

### WhatsApp
Mensagens recebidas via webhook são processadas automaticamente.

### Telegram
Bot recebe mensagens e processa com IA.

### Webhooks
```php
// WhatsApp
POST /api/webhook/whatsapp

// Telegram
POST /api/webhook/telegram/{bot_token}
```

## Configuração

### Variáveis de Ambiente

```env
GEMINI_API_KEY=sua_chave_api_aqui
```

### Modelo Utilizado

- **Modelo padrão**: `gemini-2.0-flash`
- Suporta Function Calling nativo
- Contexto de conversa com histórico

## Customização

### Adicionar Novos Tipos de Feedback

1. Atualizar migration:
```php
$table->enum('tipo', ['demanda', 'sugestao', 'opiniao', 'novo_tipo']);
```

2. Atualizar GeminiService:
```php
Schema::enumeration(
    enum: ['demanda', 'sugestao', 'opiniao', 'novo_tipo'],
    description: 'Tipo do registro'
)
```

### Modificar System Prompt

Edite a constante em `GeminiService.php`:

```php
private const SYSTEM_INSTRUCTION = 'Seu novo prompt aqui...';
```

## Troubleshooting

### IA não está chamando funções

1. Verifique se GEMINI_API_KEY está configurada
2. Confirme que o modelo suporta Function Calling
3. Revise o system prompt para ser mais claro

### Histórico de conversação não funciona

Certifique-se de passar o array `history` em cada requisição:

```javascript
history: result.history // Do resultado anterior
```

### Erro ao salvar no banco

Verifique se:
1. Migration foi executada: `php artisan migrate`
2. channel_id existe na tabela channels
3. Campos obrigatórios (tipo, descricao) estão presentes

## Exemplo Completo Node.js

Um exemplo completo de implementação em Node.js está disponível no código fornecido pelo usuário, demonstrando:

- Gerenciamento de histórico de conversação
- Function calling com duas funções (solicitar e confirmar)
- Loop de chat interativo
- Tratamento de erros

## Próximos Passos

Melhorias sugeridas:

1. **Interface Web**: Criar páginas Vue.js para visualizar feedbacks
2. **Notificações**: Alertar equipe quando novo feedback é criado
3. **Analytics**: Dashboard com estatísticas de feedbacks
4. **Categorias**: Adicionar tags e categorias aos feedbacks
5. **Priorização**: Sistema de prioridade (alta, média, baixa)
6. **Respostas**: Permitir que equipe responda diretamente aos usuários

## Referências

- [Gemini AI PHP SDK](https://github.com/google-gemini/generative-ai-php)
- [Function Calling Documentation](https://ai.google.dev/docs/function_calling)
- [Laravel Documentation](https://laravel.com/docs)

