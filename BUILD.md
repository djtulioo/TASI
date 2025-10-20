# Guia de Build e Execução Local

Este documento descreve os passos necessários para configurar e executar o projeto **Pulsar Político** em um ambiente de desenvolvimento local.

## 1. Pré-requisitos

Antes de começar, garanta que você tenha as seguintes ferramentas instaladas em sua máquina:

* **Git:** Para clonar o repositório.  
* **PHP 8.2+:** Com as extensões mbstring, openssl, pdo, tokenizer, xml.  
* **Composer:** Para gerenciamento de dependências do PHP.  
* **Node.js 18+ e npm:** Para o frontend e suas dependências.  
* **Um banco de dados:** SQLite, MySQL ou PostgreSQL.

## 2. Configuração do Ambiente

### Passo 1: Clonar o Repositório

```bash
git clone https://github.com/djtulioo/TASI.git
cd TASI
```

*Observação: Todo o projeto Laravel está dentro da pasta apps/web. Todos os comandos a seguir devem ser executados a partir deste diretório.*

### Passo 2: Instalar Dependências do Backend

Use o Composer para instalar as bibliotecas PHP necessárias.

```bash
composer install
```

### Passo 3: Instalar Dependências do Frontend

Use o npm para instalar as dependências do JavaScript.

```bash
npm install
```

### Passo 4: Configurar o Ambiente

Copie o arquivo de exemplo .env.example para criar seu próprio arquivo de configuração .env.

```bash
cp .env.example .env
```

Gere a chave da aplicação, que é essencial para a segurança.

```bash
php artisan key:generate
```

### Passo 5: Configurar o Banco de Dados

Abra o arquivo .env e configure as variáveis do banco de dados de acordo com o seu ambiente.

**Exemplo para SQLite (mais simples para iniciar):**

```conf
DB_CONNECTION=sqlite  
# DB_DATABASE=/path/to/your/database.sqlite  <- Comente ou remova esta linha
```

Crie o arquivo do banco de dados SQLite:

```bash
touch database/database.sqlite
```

**Exemplo para MySQL:**

```conf
DB_CONNECTION=mysql  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE=pulsar  
DB_USERNAME=root  
DB_PASSWORD=
```

### Passo 6: Executar as Migrations

Crie as tabelas no banco de dados executando as migrations do Laravel.

```bash
php artisan migrate
```

## 3. Executando a Aplicação

Para rodar a aplicação, você precisa iniciar dois processos simultaneamente: o servidor de desenvolvimento do Vite (para o frontend) e o servidor do Laravel (para o backend).

### Terminal 1: Iniciar o Servidor Vite

Este processo compila os assets do frontend (Vue.js, CSS) em tempo real.

```bash
npm run dev
```

### Terminal 2: Iniciar o Servidor Laravel

Este processo serve a aplicação PHP.

```bash
php artisan serve
```

Após iniciar ambos os serviços, a aplicação estará acessível em **http://localhost:8000**.