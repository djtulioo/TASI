# Sistema de Ouvidoria com Gemini AI Function Calling

## 📋 Sumário

Este documento descreve a implementação de um sistema de ouvidoria inteligente usando Gemini AI com Function Calling no Laravel. O sistema permite que usuários registrem demandas, sugestões e opiniões através de uma conversa natural com IA.

## 🎯 Funcionalidades

- ✅ Chat inteligente com Gemini AI
- ✅ Function Calling para capturar intenções do usuário
- ✅ Cadastro automático de feedback após confirmação
- ✅ Três tipos de registro: demanda, sugestão, opinião
- ✅ Histórico de conversação mantido
- ✅ API REST para integração
- ✅ Integração com WhatsApp e Telegram

## 🗄️ Estrutura do Banco de Dados

### Tabela: `feedback_entries`

```sql
CREATE TABLE feedback_entries (
  id BIGINT PRIMARY KEY,
  conversation_id BIGINT NULL,
  channel_id BIGINT NOT NULL,
  tipo ENUM('demanda', 'sugestao', 'opiniao'),
  titulo VARCHAR(255) NULL,
  descricao TEXT NOT NULL,
  sender_identifier VARCHAR(255) NULL,
  status ENUM('pendente', 'em_analise', 'resolvido', 'cancelado') DEFAULT 'pendente',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

## 🚀 Instalação

### 1. Executar a Migration

```bash
cd apps/web
php artisan migrate --force
```

### 2. Configurar Variável de Ambiente

Adicione no `.env`:

```env
GEMINI_API_KEY=sua_chave_api_aqui
```

### 3. Verificar Instalação

```bash
# Verificar se a tabela foi criada
php artisan tinker
>>> \App\Models\FeedbackEntry::count()
```

## 🧪 Testes

### Teste 1: Script PHP

Execute o script de teste PHP incluído:

```bash
php test_ouvidoria.php
```

Este script simula três cenários:
1. Cadastro bem-sucedido de uma sugestão
2. Cancelamento de um cadastro
3. Conversa casual sem cadastro

### Teste 2: CLI Node.js

Se você tiver Node.js instalado:

```bash
# Certifique-se de que o servidor Laravel está rodando
php artisan serve

# Em outro terminal
node test_ouvidoria_cli.mjs
```

### Teste 3: Via API (cURL)

```bash
# Processar mensagem
curl -X POST http://localhost:8000/api/feedback/process-message \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Gostaria de fazer uma sugestão",
    "channel_id": 1,
    "sender_identifier": "test_user"
  }'
```

### Teste 4: Via Postman/Insomnia

Importe esta requisição:

**Endpoint:** `POST /api/feedback/process-message`

**Body (JSON):**
```json
{
  "message": "Tenho uma sugestão para melhorar o sistema",
  "channel_id": 1,
  "sender_identifier": "user@example.com",
  "history": []
}
```

## 📡 API Endpoints

### Processar Mensagem

```http
POST /api/feedback/process-message
Content-Type: application/json

{
  "message": "string",
  "channel_id": "integer",
  "sender_identifier": "string",
  "conversation_id": "integer (opcional)",
  "history": "array (opcional)"
}
```

**Resposta:**
```json
{
  "success": true,
  "response": "Resposta do assistente",
  "history": [...],
  "feedback_entry": {
    "id": 1,
    "tipo": "sugestao",
    "titulo": "Título",
    "descricao": "Descrição",
    "status": "pendente"
  },
  "conversation_id": 1
}
```

### Listar Feedbacks

```http
GET /feedback-entries?channel_id=1&tipo=sugestao&status=pendente
Authorization: Bearer {token}
```

### Ver Feedback

```http
GET /feedback-entries/{id}
Authorization: Bearer {token}
```

### Atualizar Status

```http
PUT /feedback-entries/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "em_analise"
}
```

### Excluir Feedback

```http
DELETE /feedback-entries/{id}
Authorization: Bearer {token}
```

## 💬 Fluxo de Conversa

### Exemplo 1: Registrar Sugestão

```
Usuário: Gostaria de fazer uma sugestão
Bot: Claro! Ficarei feliz em ouvir sua sugestão. Pode me contar?

Usuário: Seria legal ter modo escuro no app
Bot: Ótima sugestão! Você gostaria que eu registre isso no sistema?

Usuário: Sim, pode cadastrar
Bot: Perfeito! Sua sugestão foi registrada com sucesso. [ID: 1]
```

### Exemplo 2: Registrar Demanda

```
Usuário: Tenho uma demanda urgente
Bot: Entendo. Qual é a sua demanda?

Usuário: Preciso de acesso ao módulo de relatórios
Bot: Certo, você quer que eu registre essa demanda?

Usuário: Sim
Bot: Pronto! Sua demanda foi cadastrada e será analisada pela equipe. [ID: 2]
```

### Exemplo 3: Conversa Casual

```
Usuário: Olá, como funciona o sistema?
Bot: Olá! Este é um sistema de ouvidoria onde você pode registrar
     demandas, sugestões ou opiniões. Como posso ajudar?

Usuário: Entendi, obrigado
Bot: Por nada! Se precisar registrar algo, é só me avisar.
```

## 🔧 Como Funciona Internamente

### 1. GeminiService

O `GeminiService` gerencia a comunicação com o Gemini AI:

- **generateResponse()**: Resposta simples sem function calling
- **generateResponseWithFunctionCalling()**: Resposta com function calling ativo

### 2. Function Declarations

Duas funções são declaradas para o Gemini:

#### `solicitar_cadastro_ouvidoria`
- Propõe um cadastro para o usuário
- Extrai: tipo, titulo, descricao
- Retorna status: `aguardando_confirmacao`

#### `confirmar_cadastro_ouvidoria`
- Efetiva o cadastro no banco de dados
- Requer confirmação explícita do usuário
- Cria registro em `feedback_entries`
- Retorna status: `efetivado` ou `cancelado`

### 3. Histórico de Conversa

O histórico é mantido no formato do Gemini:

```php
[
  [
    'role' => 'user',
    'parts' => [['text' => 'mensagem']]
  ],
  [
    'role' => 'model',
    'parts' => [['text' => 'resposta']]
  ]
]
```

## 🎨 Integração com Frontend

### Vue.js Component (Exemplo)

```vue
<template>
  <div class="chat-container">
    <div v-for="msg in messages" :key="msg.id" :class="msg.role">
      {{ msg.text }}
    </div>
    <input v-model="userInput" @keyup.enter="sendMessage" />
    <button @click="sendMessage">Enviar</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const messages = ref([])
const userInput = ref('')
const history = ref([])
const channelId = 1

async function sendMessage() {
  if (!userInput.value) return
  
  messages.value.push({ role: 'user', text: userInput.value })
  
  const response = await axios.post('/api/feedback/process-message', {
    message: userInput.value,
    channel_id: channelId,
    history: history.value
  })
  
  messages.value.push({ role: 'bot', text: response.data.response })
  history.value = response.data.history
  
  if (response.data.feedback_entry) {
    messages.value.push({
      role: 'system',
      text: `✅ Feedback cadastrado: ${response.data.feedback_entry.tipo}`
    })
  }
  
  userInput.value = ''
}
</script>
```

## 📊 Consultas Úteis

### Listar todos os feedbacks

```sql
SELECT * FROM feedback_entries ORDER BY created_at DESC;
```

### Contar por tipo

```sql
SELECT tipo, COUNT(*) as total 
FROM feedback_entries 
GROUP BY tipo;
```

### Feedbacks pendentes por canal

```sql
SELECT c.name, COUNT(fe.id) as total
FROM feedback_entries fe
JOIN channels c ON fe.channel_id = c.id
WHERE fe.status = 'pendente'
GROUP BY c.id, c.name;
```

### Feedbacks de um usuário específico

```sql
SELECT * FROM feedback_entries
WHERE sender_identifier = 'user@example.com'
ORDER BY created_at DESC;
```

## 🐛 Troubleshooting

### Erro: "GEMINI_API_KEY não definida"

**Solução:** Adicione a chave no `.env`:
```env
GEMINI_API_KEY=AIza...
```

### Erro: "channel_id not found"

**Solução:** Crie um canal primeiro:
```bash
php artisan tinker
>>> App\Models\Channel::create([
    'team_id' => 1,
    'name' => 'Canal de Teste',
    'type' => 'telegram'
])
```

### IA não está chamando funções

**Possíveis causas:**
1. Modelo não suporta function calling (use `gemini-2.0-flash` ou superior)
2. System prompt não está claro o suficiente
3. Mensagem do usuário é ambígua

**Solução:** Seja mais explícito no system prompt:
```php
private const SYSTEM_INSTRUCTION = 'SEMPRE use solicitar_cadastro_ouvidoria quando...';
```

### Histórico não está funcionando

**Solução:** Certifique-se de retornar e passar o histórico:
```javascript
// Guardar histórico da resposta anterior
const history = result.history

// Passar no próximo request
await fetch('/api/feedback/process-message', {
  body: JSON.stringify({ message, history })
})
```

## 📚 Documentação Adicional

- **Documentação completa:** `OUVIDORIA_GEMINI_FUNCTION_CALLING.md`
- **Gemini PHP SDK:** [google-gemini/generative-ai-php](https://github.com/google-gemini/generative-ai-php)
- **Function Calling Guide:** [ai.google.dev](https://ai.google.dev/docs/function_calling)

## 🤝 Contribuindo

Para adicionar novos tipos de feedback ou melhorias:

1. Atualize a migration
2. Modifique o GeminiService
3. Adicione testes
4. Atualize esta documentação

## 📝 Licença

Este projeto está sob a mesma licença do projeto principal.

---

**Desenvolvido por:** Anderson Tulio
**Data:** 02/12/2025
**Versão:** 1.0.0

