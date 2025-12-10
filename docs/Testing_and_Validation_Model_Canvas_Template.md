# **Canvas de Testes e Validação**

Fase: 3\. Produção

Projeto: Sistema Pulsar

## **1\. Testes Automatizados (Evidências no Repositório)**

| Tipo | Arquivo de Teste | O que valida? |
| :---- | :---- | :---- |
| **Integração IA** | tests/Feature/ChatAnalysisTest.php | Verifica se o prompt é montado corretamente e se a API do Gemini responde ao contexto injetado. |
| **Webhook** | tests/Feature/TelegramWebhookTest.php | Simula payloads do Telegram para garantir que mensagens criam FeedbackEntries no banco. |
| **Segurança** | tests/Feature/AuthenticationTest.php | Garante que apenas gestores autenticados acessem o painel de análise. |
| **Lógica** | tests/Feature/CreateTeamTest.php | Valida a separação de dados entre diferentes times/instituições (Multi-tenancy). |

## **2\. Validação Funcional (UAT)**

* **Fluxo de Chat:** Validado que perguntas em linguagem natural ("Quais os problemas de hoje?") retornam resumos coerentes com os dados do *seeder*.  
* **Tratamento de Erros:** Validado o comportamento do sistema quando a API do Gemini falha (Fallback gracioso implementado no GeminiService).

## **3\. Status de Produção**

* Host funcional no Google Cloud Run.  
* Pipeline de CI/CD configurado (cloudbuild.yaml) para deploy automático.  