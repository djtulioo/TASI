# **Solution Ideation Model Canvas**

Referência: Canvas de Ideação de Soluções (Ideação)

Projeto: Sistema Pulsar

## **1\. Divergência (Brainstorming)**

* **Ideia A:** App mobile nativo com gamificação. *(Descartado: Alta barreira de instalação)*.  
* **Ideia B:** Dashboard BI estático (PowerBI style). *(Descartado: Difícil interpretação para leigos)*.  
* **Ideia C:** Chatbot Passivo (Coleta) \+ Interface RAG Ativa (Análise). *(Selecionada)*.

## **2\. Solução Escolhida**

Plataforma de Ouvidoria Inteligente baseada em RAG (Retrieval-Augmented Generation).

O sistema atua em duas pontas: coleta passiva via apps de mensagem (onde o usuário já está) e análise ativa via chat web para o gestor.

## **3\. Stack Tecnológico**

* **Backend:** Laravel 11 (Gestão de filas e Webhooks).  
* **Frontend:** Vue.js \+ Inertia (SPA reativa).  
* **IA:** Google Gemini 1.5 Flash (Janela de contexto longa e baixo custo).  
* **Infra:** Google Cloud Run (Serverless).

## **4\. Diferenciais**

* **Invisible App:** Para o aluno, é apenas um contato no Telegram.  
* **Chat com Dados:** O gestor não analisa gráficos, ele faz perguntas ("Por que reclamam da comida?") e obtém respostas sintetizadas.