# Guia de Build e Execução Local

Este documento descreve os passos necessários para configurar e executar o projeto **Pulsar Político** em um ambiente de desenvolvimento local.

## 1. Pré-requisitos

Antes de começar, garanta que você tenha as seguintes ferramentas instaladas em sua máquina:

- **Git:** Para clonar o repositório.
- **Python 3.9+:** Para o backend e os scripts de coleta/análise de dados.
- **Node.js 18+ e npm:** Para o frontend.
- **Docker (Opcional):** Para executar o projeto em contêineres, facilitando a configuração.

## 2. Configuração do Ambiente

### Passo 1: Clonar o Repositório

```bash
git clone https://github.com/SEU_USUARIO/pulsar-politico.git
cd pulsar-politico
```

### Passo 2: Configurar o Backend (Python)

Navegue até o diretório do backend e instale as dependências.

```bash
cd backend
python -m venv venv
source venv/bin/activate  # No Windows: venv\Scripts\activate
pip install -r requirements.txt
```

### Passo 3: Configurar o Frontend (Node.js)

Navegue até o diretório do frontend e instale as dependências.

```bash
cd frontend
npm install
```

## 3. Executando a Aplicação

### Backend

Com o ambiente virtual ativado, inicie o servidor backend (o comando pode variar):

```bash
cd backend
uvicorn main:app --reload
```

### Frontend

Inicie o servidor de desenvolvimento do frontend:

```bash
cd frontend
npm run dev
```

Após iniciar ambos os serviços, a aplicação estará acessível em `http://localhost:3000` (ou outra porta definida para o frontend).
