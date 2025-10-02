# 🔧 Configuração de Variáveis de Ambiente no Cloud Run

## 📋 Opções Disponíveis

### 1️⃣ Método Rápido - Via Script (Recomendado)

```bash
# 1. Copiar o arquivo de exemplo
cp .env.cloudrun.example .env.cloudrun

# 2. Editar com suas configurações
nano .env.cloudrun
# ou
code .env.cloudrun

# 3. Executar o script de atualização
./update-env.sh
```

### 2️⃣ Método Manual - Via gcloud

#### Atualizar variáveis normais:

```bash
gcloud run services update laravel-app \
    --region=us-central1 \
    --set-env-vars="
APP_ENV=production,
APP_DEBUG=false,
APP_URL=https://laravel-app-772767884521.us-central1.run.app,
ASSET_URL=https://laravel-app-772767884521.us-central1.run.app,
LOG_CHANNEL=stderr,
SESSION_DRIVER=database,
CACHE_DRIVER=database,
QUEUE_CONNECTION=database
"
```

#### Atualizar secrets (dados sensíveis):

```bash
# Primeiro, criar o secret no Secret Manager
echo -n "sua-senha-aqui" | gcloud secrets create DB_PASSWORD --data-file=-

# Depois, adicionar ao Cloud Run
gcloud run services update laravel-app \
    --region=us-central1 \
    --set-secrets="DB_PASSWORD=DB_PASSWORD:latest"
```

### 3️⃣ Método via Console Web

1. Acesse: https://console.cloud.google.com/run
2. Clique no service `laravel-app`
3. Clique em "EDIT & DEPLOY NEW REVISION"
4. Vá para a aba "Variables & Secrets"
5. Adicione as variáveis e secrets
6. Clique em "DEPLOY"

## 📝 Variáveis Importantes para Configurar

### ✅ Obrigatórias:

- `APP_KEY` (secret) - Já configurado
- `APP_URL` - URL da aplicação
- `ASSET_URL` - URL dos assets

### 🔐 Dados Sensíveis (usar Secret Manager):

- `APP_KEY` ✅ (já configurado)
- `DB_PASSWORD` - Senha do banco de dados
- `MAIL_PASSWORD` - Senha do email
- Qualquer API key ou token

### ⚙️ Configurações de Database:

```bash
DB_CONNECTION=pgsql
DB_HOST=/cloudsql/PROJECT_ID:REGION:INSTANCE_NAME
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel-user
```

### 📧 Configurações de Email:

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seu-dominio.app
```

## 🚀 Comandos Úteis

### Ver variáveis atuais:

```bash
gcloud run services describe laravel-app \
    --region=us-central1 \
    --format="table(spec.template.spec.containers[0].env)"
```

### Adicionar uma variável:

```bash
gcloud run services update laravel-app \
    --region=us-central1 \
    --set-env-vars="NOVA_VARIAVEL=valor"
```

### Remover uma variável:

```bash
gcloud run services update laravel-app \
    --region=us-central1 \
    --remove-env-vars="VARIAVEL_PARA_REMOVER"
```

### Atualizar via script do deploy:

```bash
./deploy.sh env KEY=VALUE KEY2=VALUE2
```

## 🔐 Gerenciamento de Secrets

### Criar um novo secret:

```bash
# Interativo
gcloud secrets create NOME_DO_SECRET

# Via arquivo
echo -n "valor-secreto" | gcloud secrets create NOME_DO_SECRET --data-file=-

# Via string
echo -n "valor-secreto" | gcloud secrets create NOME_DO_SECRET --data-file=-
```

### Atualizar um secret existente:

```bash
echo -n "novo-valor" | gcloud secrets versions add NOME_DO_SECRET --data-file=-
```

### Listar secrets:

```bash
gcloud secrets list
```

### Adicionar secret ao Cloud Run:

```bash
gcloud run services update laravel-app \
    --region=us-central1 \
    --set-secrets="NOME_DA_ENV=NOME_DO_SECRET:latest"
```

## 🎯 Exemplo Completo de Setup

```bash
# 1. Criar secrets necessários
echo -n "base64:sua-app-key-aqui" | gcloud secrets create APP_KEY --data-file=-
echo -n "senha-do-banco" | gcloud secrets create DB_PASSWORD --data-file=-
echo -n "senha-do-email" | gcloud secrets create MAIL_PASSWORD --data-file=-

# 2. Atualizar Cloud Run com variáveis e secrets
gcloud run services update laravel-app \
    --region=us-central1 \
    --set-env-vars="
APP_ENV=production,
APP_DEBUG=false,
APP_URL=https://seu-dominio.app,
ASSET_URL=https://seu-dominio.app,
LOG_CHANNEL=stderr,
SESSION_DRIVER=database,
CACHE_DRIVER=database,
QUEUE_CONNECTION=database,
DB_CONNECTION=pgsql,
DB_HOST=/cloudsql/pulsar-politico:us-central1:laravel-db,
DB_PORT=5432,
DB_DATABASE=laravel,
DB_USERNAME=laravel-user,
MAIL_MAILER=smtp,
MAIL_HOST=smtp.gmail.com,
MAIL_PORT=587,
MAIL_USERNAME=seu-email@gmail.com,
MAIL_ENCRYPTION=tls,
MAIL_FROM_ADDRESS=noreply@seu-dominio.app
" \
    --set-secrets="
APP_KEY=APP_KEY:latest,
DB_PASSWORD=DB_PASSWORD:latest,
MAIL_PASSWORD=MAIL_PASSWORD:latest
"
```

## ⚠️ Dicas Importantes

1. **Nunca commite secrets no Git** - Use `.env.cloudrun` em `.gitignore`
2. **Use Secret Manager para dados sensíveis** - Senhas, API keys, tokens
3. **Teste em staging primeiro** - Antes de atualizar produção
4. **Documente suas variáveis** - Mantenha lista atualizada
5. **Use versões dos secrets** - Permite rollback fácil

## 🔄 Workflow Recomendado

```bash
# 1. Copiar e configurar arquivo de env
cp .env.cloudrun.example .env.cloudrun
nano .env.cloudrun

# 2. Criar secrets sensíveis no Secret Manager
./deploy.sh secret DB_PASSWORD "senha-aqui"

# 3. Atualizar variáveis de ambiente
./update-env.sh

# 4. Verificar se está tudo ok
./deploy.sh status

# 5. Ver logs
./deploy.sh logs
```

## 📚 Links Úteis

- [Cloud Run Environment Variables](https://cloud.google.com/run/docs/configuring/environment-variables)
- [Secret Manager Documentation](https://cloud.google.com/secret-manager/docs)
- [Laravel Configuration](https://laravel.com/docs/configuration)

