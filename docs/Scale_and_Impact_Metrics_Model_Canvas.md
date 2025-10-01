# Canvas de Métricas de Escala e Impacto - Pulsar Político

Este documento organiza as métricas essenciais para monitorar a operação, o desempenho e o impacto da plataforma Pulsar Político.

### 1. Objetivo do Monitoramento

- Garantir a eficácia, a eficiência e a precisão da plataforma na análise de percepção pública. O monitoramento visa validar se a ferramenta está fornecendo insights acionáveis e de alta qualidade para as equipes políticas, alinhado aos objetivos estratégicos do projeto.

### 2. Métricas de Uso

- **Usuários Ativos (Diário/Mensal):** Número de assessores/políticos que acessam a plataforma.
- **Termos Monitorados:** Quantidade de políticos, temas ou palavras-chave sendo ativamente rastreados.
- **Frequência de Acesso:** Média de logins por usuário por semana.
- **Relatórios Gerados/Exportados:** Número de relatórios que os usuários geram a partir dos dados.

### 3. Métricas de Desempenho

- **Latência da Coleta:** Tempo entre a publicação de um post (ex: tweet) e sua exibição na plataforma (end-to-end).
- **Acurácia do Modelo de Sentimento:** Percentual de acerto (Precisão, Recall, F1-Score) do modelo de IA, medido contra um conjunto de dados de validação rotulado manualmente.
- **Taxa de Processamento:** Número de menções (tweets, posts, etc.) processadas por hora.
- **Tempo de Resposta da API:** Latência (em ms) para carregar os dados nos dashboards.
- **Uptime do Sistema:** Disponibilidade da plataforma (ex: 99,5%).

### 4. Métricas de Impacto no Negócio (para o cliente)

- **Redução no Tempo de Análise:** Comparativo de horas/homem gastas para gerar um relatório de percepção antes e depois da ferramenta.
- **Velocidade de Detecção de Crise:** Tempo médio para a plataforma alertar sobre um pico de sentimento negativo relevante.
- **Adoção da Ferramenta:** Percentual da equipe de comunicação do cliente que utiliza a plataforma como sua principal fonte de insights.

### 5. Métricas de Satisfação do Usuário

- **Net Promoter Score (NPS):** "Em uma escala de 0 a 10, o quanto você recomendaria o Pulsar Político a um colega?"
- **Customer Satisfaction Score (CSAT):** Pesquisas pontuais após o uso de uma feature chave (ex: "O resumo gerado foi útil?").
- **Taxa de Retenção de Clientes:** Percentual de clientes que renovam o uso da plataforma após um período de teste/contrato.

### 6. Ferramentas de Monitoramento

- **Uso e Desempenho:** Grafana, Prometheus (para infraestrutura), Datadog ou Sentry (para performance de API e frontend).
- **Satisfação:** Ferramentas de pesquisa in-app (ex: Hotjar, Pendo) ou formulários simples.
- **Impacto no Negócio:** Entrevistas qualitativas e questionários com os clientes.

### 7. Benchmarks (Metas para o MVP)

- **Latência da Coleta:** < 5 minutos.
- **Acurácia do Modelo de Sentimento:** > 80% (F1-Score para 3 classes).
- **Uptime do Sistema:** > 99%.
- **CSAT:** > 4/5 em features chave.

### 8. Acompanhamento de Tendências

- Dashboards internos (Grafana/Kibana) para visualização em tempo real das métricas de desempenho.
- Relatórios semanais automáticos sobre métricas de uso e saúde do sistema.
- Análise trimestral dos resultados de NPS e CSAT para guiar o roadmap de produto.

### 9. Ações Baseadas nas Métricas

- **Acurácia < 80%:** Iniciar um ciclo de re-treinamento do modelo de IA com mais dados rotulados.
- **Latência da Coleta > 5 min:** Investigar gargalos nos workers de coleta ou no pipeline de processamento.
- **Baixa Adoção de uma Feature:** Realizar entrevistas com usuários para entender a usabilidade e o valor percebido.

### 10. Relatórios e Compartilhamento

- **Equipe de Desenvolvimento:** Dashboard em tempo real com métricas de sistema.
- **Stakeholders (Professor):** Relatório mensal consolidando as principais métricas de uso, desempenho e satisfação, apresentado nos checkpoints do projeto.
