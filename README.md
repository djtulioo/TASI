# **Pulsar Político - Plataforma de Ouvidoria Inteligente**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Pulsar Político** é uma plataforma de ouvidoria inteligente que conecta populações (cidadãos, clientes, colaboradores) diretamente aos administradores responsáveis, utilizando o WhatsApp como canal principal de comunicação.

Através de um chatbot conversacional, o sistema coleta reclamações, feedbacks e alertas, organizando os dados e utilizando IA para gerar métricas, insights e sugestões de melhoria de forma contínua. O objetivo é transformar a escuta em inteligência acionável, ajudando gestores a compreender o que realmente "pulsa" em sua comunidade ou organização.

Este projeto está sendo desenvolvido como parte da disciplina [IF1006 - Transformação Digital com IA](https://github.com/assertlab/ai-design-engineering), da [Universidade Federal de Pernambuco (UFPE)](https://www.ufpe.br).

---

## 🎯 Objetivos do Projeto

- **Canal de Comunicação Acessível:** Utilizar o WhatsApp como um canal universal e de fácil acesso para que os cidadãos possam se comunicar.
- **Coleta e Estruturação de Dados:** Capturar as interações dos usuários e, através de IA, categorizar e estruturar as informações para análise.
- **Geração de Insights para Gestores:** Oferecer um console administrativo com dashboards, métricas e relatórios que permitam uma tomada de decisão mais rápida e baseada em dados.
- **Melhora Contínua:** Fornecer um ciclo de feedback contínuo que ajude a aprimorar a gestão e a comunicação com a população.

---

## 👥 Equipe e Papéis

| Membro             | Papel Principal      | GitHub                               |
| ------------------ | ------------------------------------ | ------------------------------------ |
| Dário Vasconcelos  | Líder da Equipe / Desenvolvedor Backend | [@dariogsv](https://github.com/dariogsv) |
| Túlio Siqueira    | Desenvolvedor Frontend / Product Owner | [@djtulioo](https://github.com/djtulioo) |
| Neto               | Desenvolvedor / Engenheiro de Dados    | [](https://github.com/)   |


---

## 🛠️ Estrutura e Arquitetura

A plataforma é composta por dois componentes principais: um **aplicativo web em Laravel** que serve como console para o administrador e um **serviço de chatbot** que interage com os usuários no WhatsApp.

```mermaid
graph TD
    %% Definição de Estilos
    classDef user fill:#87CEEB,stroke:#333,stroke-width:2px,color:#222;
    classDef system fill:#90EE90,stroke:#333,stroke-width:2px,color:#222;
    classDef external fill:#D3D3D3,stroke:#333,stroke-width:2px,color:#222;

    subgraph "Usuários"
        cidadao("fa:fa-user Cidadão")
        administrador("fa:fa-user-tie Administrador")
    end

    subgraph "Sistema Pulsar Político"
        whatsapp_api("fa:fa-whatsapp WhatsApp API")
        chatbot_service{{"fa:fa-robot<br>Serviço de Chatbot<br><size:10>[Processamento de Mensagens]</size>"}}
        webapp_laravel{{"fa:fa-laptop-code<br>Aplicação Web (Laravel)<br><size:10>[Console do Administrador]</size>"}}
        database("fa:fa-database Banco de Dados")
    end

    %% Relacionamentos
    cidadao -- "Envia mensagem" --> whatsapp_api
    whatsapp_api -- "Recebe e envia mensagens" --> chatbot_service
    chatbot_service -- "Processa e armazena<br>informações" --> database
    administrador -- "Acessa dashboards,<br>métricas e insights" --> webapp_laravel
    webapp_laravel -- "Lê dados para<br>análise" --> database


    %% Aplicação dos Estilos
    class cidadao,administrador user;
    class chatbot_service,webapp_laravel,database system;
    class whatsapp_api external;
```

---

## 🚀 Começando

Para executar o projeto localmente, siga as instruções detalhadas em nosso guia de build.

➡️ **[Guia de Build e Execução](BUILD.md)**

---

## 🤝 Como Contribuir

Estamos abertos a contribuições! Se você tem interesse em melhorar a plataforma, seja corrigindo um bug ou adicionando uma nova funcionalidade, por favor, leia nosso guia de contribuição.

➡️ **[Guia de Contribuição](CONTRIBUTING.md)**

---

## 🔗 Links Úteis

| Recurso                | Link                                                              |
| ---------------------- | ----------------------------------------------------------------- |
| **Quadro de Tarefas**  | GitHub Projects |
| **Registro de Issues** | GitHub Issues |
| **Documentação Geral** | `/docs` |
| **Definições do Projeto** | `/docs/reference` |