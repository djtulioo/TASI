# **Pulsar \- Sistema de Ouvidoria Inteligente com IA Generativa**

Instituição: UFPE  
Disciplina: Tópicos Avançados em Sistemas de Informação (TASI)  
Semestre: 2025.2  
Professor: Vinicius Cardoso Garcia  
**Equipe:**

* **Líder Técnico:** Dário Vasconcelos  
* Túlio Siqueira  
* Eraldo Cassimiro  
* Mateus Olegário

Link do Repositório: https://www.google.com/search?q=https://github.com/djtulioo/tasi

Data da Entrega: 10 de Dezembro de 2025

# **1\. Introdução**

### **Contextualização do Problema de Negócio**

Comunidades e Instituições de médio e grande porte enfrentam dificuldades crônicas na gestão de feedbacks. Os canais tradicionais (caixas de sugestão físicas, e-mails esparsos ou formulários longos) geram dados desestruturados e de difícil análise. Gestores frequentemente perdem tempo compilando planilhas manuais ou, pior, ignoram tendências críticas de insatisfação por falta de visibilidade.

### **Objetivos da Solução Proposta**

O **Pulsar** tem como objetivo centralizar, gerenciar e analisar feedbacks institucionais de forma omnicanal.

* **Geral:** Transformar dados brutos de reclamações e sugestões em inteligência acionável para a tomada de decisão.  
* **Específicos:**  
  1. Reduzir a barreira de entrada para o usuário final através de integrações com Telegram e WhatsApp.  
  2. Automatizar a análise de sentimento e categorização de tópicos.  
  3. Substituir a necessidade de interpretação complexa de gráficos por uma interface de chat em linguagem natural.

### **Justificativa do Uso de IA e LLMs**

A natureza dos dados de ouvidoria é fundamentalmente textual e desestruturada (comentários livres, áudios transcritos). Métodos tradicionais de BI (Business Intelligence) são excelentes para números, mas falham em interpretar nuances de linguagem, sarcasmo ou contexto. O uso de **LLMs (Large Language Models)**, especificamente o Google Gemini, permite:

1. **Interpretação Semântica:** Entender que "o almoço estava gelado" e "serviram comida fria" referem-se ao mesmo problema.  
2. **RAG (Retrieval-Augmented Generation):** Permitir que o gestor faça perguntas complexas aos dados (ex: "Qual a evolução das queixas sobre segurança no último mês?") e receba respostas sintetizadas baseadas em fatos.

# **2\. Metodologia**

### **Aplicação da Metodologia**

O projeto seguiu uma abordagem ágil adaptada, com ciclos iterativos de desenvolvimento. A metodologia **Sinfonia** foi utilizada para estruturar a integração da Inteligência Artificial, focando primeiro na identificação do domínio e das fontes de dados antes de partir para a codificação.

### **Gestão do Trabalho em Equipe**

A equipe utilizou o GitHub para versionamento e gestão de tarefas. O fluxo de trabalho baseou-se em *Feature Branches*, onde novas funcionalidades (como a integração do Telegram ou o módulo de Chat) eram desenvolvidas isoladamente e integradas via Pull Requests após testes automatizados.

### **Etapas e Marcos Principais**

1. **Fase 1:** Levantamento de requisitos e definição da arquitetura (Docker, Laravel, Vue).  
2. **Fase 2:** Desenvolvimento do CRUD básico de Feedbacks e Canais.  
3. **Fase 3:** Integração Omnicanal (Webhook Telegram).  
4. **Fase 4 (A Pivotagem):** Substituição dos dashboards estáticos pela interface de Chat com IA.  
5. **Fase 5:** Refinamento, testes automatizados e deploy no Google Cloud Run.

# **3\. Discussões Técnicas e Estratégicas**

### **Decisões Arquiteturais: A Pivotagem para Chat**

A decisão mais crítica do projeto foi a transição de **Dashboards Gráficos** para **Chat Conversacional (RAG)**. Inicialmente, o sistema exibia contagens simples. Percebeu-se que saber que houve "15 reclamações" era menos valioso do que saber que "as reclamações aumentaram devido à mudança no fornecedor de café". A arquitetura foi adaptada para indexar feedbacks e enviá-los como *contexto* para o Gemini, permitindo respostas qualitativas.

### **Integrações**

* **Telegram:** Implementada via Webhooks para resposta em tempo real. O desafio foi lidar com a assincronicidade e garantir que o bot respondesse corretamente ao comando `/start`.  
* **Google Gemini:** Utilizado o modo `gemini-2.0-flash` para balancear custo e latência.

### **Desafios Superados**

1. **Limitação de Contexto (Tokens):** Enviar todos os feedbacks do mês estourava o limite da IA.  
   * *Solução:* Implementação de filtros de data (`DateRangePicker.vue`) e pré-filtragem no banco de dados antes do envio para a API.  
2. **Ambiente de Deploy:** Configurar o Laravel no Google Cloud Run exigiu ajustes no `Dockerfile` e `supervisord.conf` para rodar Nginx e PHP-FPM no mesmo container stateless.

# **5\. Considerações Éticas**

### **Riscos e Vieses**

* **Alucinação da IA:** Risco da IA inventar fatos não presentes nos feedbacks.  
  * *Mitigação:* Engenharia de prompt restritiva ("Responda apenas com base nos dados fornecidos") e logs de auditoria.  
* **Privacidade:** O envio de mensagens pessoais para uma nuvem de terceiros (Google).  
  * *Mitigação:* Anonimização dos dados antes do envio para a API da IA (remoção de nomes e telefones na camada de `Service`).

# **7\. Lições Aprendidas e Reflexões Finais**

### **Aprendizados**

A equipe aprendeu que a tecnologia (IA) deve servir à usabilidade. Implementar IA apenas por "hype" não agrega valor; a IA só se tornou útil quando resolveu o problema da complexidade de análise de dados.

### **Avaliação da Proposta de Valor**

O **Pulsar** entrega valor ao reduzir o tempo cognitivo necessário para entender o clima organizacional. O que levava horas de leitura de planilhas agora é resolvido com uma pergunta no chat.

### **Sugestões de Melhoria**

1. Implementar **Vector Database (RAG Vetorial)** para buscas semânticas mais precisas em longos períodos.  
2. Finalizar a integração com WhatsApp Business API.  
3. Criar agentes autônomos que alertam ativamente o gestor sobre picos de anomalia (ex: "Detectado aumento súbito de reclamações sobre X").

# **8\. Referências**

1. **Laravel Documentation:** https://laravel.com/docs  
2. **Google AI Studio (Gemini API):** https://ai.google.dev  
3. Documentação interna do projeto (`BUILD.md`, `DEPLOY_CLOUD_RUN.md`).

# **9\. Apêndices**

Plataforma Pulsar:https://laravel-app-fiqolfjcjq-uc.a.run.app
Apresentação: [Apresentação.pdf](./Apresentação.pdf)