# Canvas de Métricas de Escala e Impacto - Pulsar Político

Este documento organiza as métricas para monitorar a operação, o desempenho e o impacto da plataforma Pulsar Político, alinhada à sua missão de ser uma ponte de comunicação entre cidadãos e gestores.

### 1. Objetivo do Monitoramento

- Medir a eficácia da plataforma em criar uma ponte de comunicação entre cidadãos e gestores públicos. O objetivo é garantir que a ferramenta colete feedback representativo, gere insights acionáveis e contribua para uma tomada de decisão mais precisa e baseada em evidências.

### 2. Métricas de Uso

- **Engajamento Cidadão:**
  - Número de conversas iniciadas/concluídas por dia/semana.
  - Taxa de conclusão das conversas (funil de engajamento).
  - Número de cidadãos únicos interagindo com o chatbot.
- **Uso pelo Gestor Público:**
  - Usuários ativos (gestores/analistas) na plataforma.
  - Frequência de acesso aos dashboards.
  - Número de relatórios e insights gerados/exportados.
  - Volume de documentos (atas, transcrições) submetidos para análise.

### 3. Métricas de Desempenho

- **Qualidade da IA:**
  - Acurácia da categorização de tópicos (ex: saúde, segurança).
  - Qualidade da sumarização (avaliada por humanos).
  - Taxa de respostas não compreendidas pelo chatbot.
- **Performance Técnica:**
  - Tempo de resposta do chatbot.
  - Latência de carregamento dos dashboards e processamento de documentos.
  - Uptime do Sistema (ex: 99,5%).

### 4. Métricas de Impacto (para a Gestão Pública)

- **Qualitativas (via entrevistas):**
  - Número de decisões/políticas públicas que foram influenciadas pelos insights da plataforma.
  - Percepção de clareza dos gestores sobre as prioridades da população.
  - Valor percebido ao analisar canais existentes (ex: "Isso nos deu uma nova visão sobre as audiências públicas").
- **Quantitativas:**
  - Redução no tempo para identificar as principais demandas populares.
  - Adoção da ferramenta como fonte primária para planejamento.

### 5. Métricas de Satisfação do Usuário

- **Para o Cidadão (no final do chat):**
  - CSAT: "Quão fácil foi expressar sua opinião?" (escala de 1 a 5).
  - Feedback qualitativo: "Você se sentiu ouvido?".
- **Para o Gestor Público (na plataforma):**
  - NPS: "Em uma escala de 0 a 10, o quanto você recomendaria o Pulsar Político a outro órgão público?".
  - Utilidade do Insight: "Este resumo foi útil para sua análise?" (Sim/Não).

### 6. Ferramentas de Monitoramento

- **Uso e Desempenho:** Ferramentas de analytics (ex: Google Analytics), logs da aplicação, Sentry, Grafana.
- **Satisfação:** Ferramentas de pesquisa in-app (ex: Hotjar, Pendo) ou formulários simples.
- **Impacto na Gestão:** Entrevistas qualitativas e questionários com os gestores.

### 7. Benchmarks (Metas para o MVP)

- **Acurácia da Categorização:** > 85% (F1-Score).
- **Taxa de Conclusão do Chat:** > 70%.
- **Uptime do Sistema:** > 99%.
- **CSAT (Cidadão):** > 4/5.

### 8. Acompanhamento de Tendências

- Relatórios semanais sobre métricas de uso e engajamento (cidadão e gestor).
- Análise trimestral dos resultados de NPS e CSAT para guiar o roadmap de produto.

### 9. Ações Baseadas nas Métricas

- **Acurácia < 85%:** Iniciar um ciclo de re-treinamento do modelo de IA com mais dados rotulados por humanos.
- **Baixa Taxa de Conclusão do Chat:** Revisar o fluxo conversacional, simplificar perguntas ou reduzir o número de etapas.
- **Baixo CSAT/NPS:** Realizar entrevistas com os usuários (cidadãos ou gestores) para entender os pontos de atrito e o valor percebido.

### 10. Relatórios e Compartilhamento

- **Equipe de Desenvolvimento:** Dashboard em tempo real com métricas de sistema.
- **Stakeholders (Professor):** Relatório mensal consolidando as principais métricas de uso, desempenho e satisfação, apresentado nos checkpoints do projeto.
