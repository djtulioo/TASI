# Guia de Build e Execução Local

Este documento descreve os passos para configurar e executar o projeto **TASI** localmente e, opcionalmente, expor o servidor de desenvolvimento do Vite (`npm run dev`) via ngrok para testes externos.

## 1. Pré-requisitos

Antes de começar, garanta que você tenha as seguintes ferramentas instaladas em sua máquina:

* Git (para clonar o repositório)
* PHP 8.2+ (com extensões: mbstring, openssl, pdo, tokenizer, xml)
* Composer (dependências PHP)
* Node.js 18+ e npm (dependências do frontend)
* Um banco de dados (SQLite, MySQL ou PostgreSQL)
* Opcional para acesso externo: ngrok (conta e authtoken configurados)

## 2. Configuração do Ambiente

### Passo 1: Clonar o repositório e posicionar no app web

```powershell
git clone https://github.com/djtulioo/TASI.git
cd TASI\apps\web
```

Observação: o aplicativo Laravel fica em `apps/web`. A partir de agora, execute os comandos dentro desse diretório.

### Passo 2: Instalar dependências do backend (PHP)

```powershell
composer install
```

### Passo 3: Instalar dependências do frontend

```powershell
npm install
```

### Passo 4: Configurar o ambiente (.env)

Copie o arquivo de exemplo `.env.example` para `.env` e gere a chave da aplicação:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### Passo 5: Configurar o banco de dados

Abra o arquivo .env e configure as variáveis do banco de dados de acordo com o seu ambiente.

Exemplo (rápido) com SQLite:

```conf
DB_CONNECTION=sqlite
# DB_DATABASE=/path/to/your/database.sqlite  <- deixe comentado
```

Crie o arquivo do banco SQLite:

```powershell
New-Item -ItemType File .\database\database.sqlite -Force | Out-Null
```

Exemplo para MySQL:

```conf
DB_CONNECTION=mysql  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE=pulsar  
DB_USERNAME=root  
DB_PASSWORD=
```

### Passo 6: Executar as migrations

Crie as tabelas no banco de dados executando as migrations do Laravel.

```powershell
php artisan migrate
```

## 3. Executando a aplicação (local)

Você precisa iniciar dois processos: o servidor de desenvolvimento do Vite (frontend) e o servidor do Laravel (backend).

### Terminal 1 — Vite (frontend)

```powershell
npm run dev
```

### Terminal 2 — Laravel (backend)

```powershell
php artisan serve
```

Aplicação: http://localhost:8000

Assets/HMR (Vite): http://localhost:5173

> Dica: o `vite.config.js` já está com `server.host = true` e `cors = true` para facilitar acesso na rede local.

---

## 4. Expor o Vite (npm run dev) via ngrok

Use esta opção quando você precisa acessar os assets do Vite e receber HMR a partir de um dispositivo externo (por exemplo, celular) ou compartilhar a interface em rede externa.

### 4.1. Pré-requisitos

- ngrok instalado e logado (com seu authtoken configurado)

Instalação no Windows (PowerShell): consulte https://ngrok.com/download e execute o instalador. Depois, faça login:

```powershell
ngrok config add-authtoken <SEU_AUTHTOKEN>
```

### 4.2. Iniciar Vite e abrir o túnel

Em dois terminais dentro de `apps/web`:

1) Inicie o Vite normalmente:

```powershell
npm run dev
```

2) Abra o túnel para a porta do Vite (5173):

```powershell
ngrok http http://localhost:5173
```

Copie a URL gerada (ex.: `https://<subdominio>.ngrok-free.app`). Essa será a URL externa do Vite.

### 4.3. Ajustar o HMR para funcionar via ngrok

Para que o Hot Module Replacement (HMR) funcione fora da sua máquina, o cliente Vite precisa saber qual host externo usar. Há duas abordagens (escolha UMA):

— Abordagem A: ajuste temporário no `vite.config.js`

Edite `apps/web/vite.config.js` na seção `server.hmr` sempre que abrir um túnel novo:

```js
server: {
	host: true,
	cors: true,
	port: 5173,
	hmr: {
		host: '<SEU_SUBDOMINIO>.ngrok-free.app',
		protocol: 'wss',
		clientPort: 443,
	},
}
```

Reinicie o `npm run dev` após a alteração.

— Abordagem B: variável de ambiente (evita editar o arquivo)

Crie/edite `.env` e informe a URL do dev server externo para o plugin do Laravel Vite reconhecer (mantendo o `vite.config.js` padrão):

```conf
VITE_DEV_SERVER_URL=https://<SEU_SUBDOMINIO>.ngrok-free.app
```

Em seguida, reinicie `npm run dev`.

> Observações importantes:
> - Se a página for acessada por HTTPS (ngrok), use `protocol: 'wss'` e `clientPort: 443` para o HMR.
> - Cada vez que o ngrok gerar um novo subdomínio, atualize o host de HMR ou a `VITE_DEV_SERVER_URL`.
> - Evite commitar mudanças com subdomínios pessoais. Prefira a Abordagem B (variável de ambiente) em equipes.

### 4.4. Acessando externamente

- Acesse a aplicação Laravel pelo seu endereço local (ex.: http://192.168.x.x:8000) ou por outro túnel voltado ao backend, se necessário.
- Os assets/HMR serão servidos pelo domínio do ngrok (ex.: `https://<subdominio>.ngrok-free.app`).

Se sua página não carregar CSS/JS ou o HMR não conectar, verifique no console do navegador se o WebSocket está tentando `wss://<subdominio>.ngrok-free.app` e se o host/porta batem com sua configuração.

---

## 5. Problemas comuns

- HMR não conecta externamente: confira `server.hmr` (host/protocol/port) ou `VITE_DEV_SERVER_URL` e reinicie o Vite.
- Mixed content (conteúdo misto): se estiver acessando via HTTPS (ngrok), use `wss` no HMR.
- 404 de assets via ngrok: confirme que o túnel está apontando para `http://localhost:5173` e que o Vite está rodando.
- Banco de dados não inicializa: garanta que `.env` está correto e que você rodou `php artisan migrate`.