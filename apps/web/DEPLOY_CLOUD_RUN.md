# Deploy do Laravel no Google Cloud Run

Este guia explica como fazer o deploy da aplicação Laravel com Inertia.js, Jetstream e Vue.js no Google Cloud Run.

## 📋 Pré-requisitos

1. **Google Cloud Account** com billing ativado
2. **Google Cloud CLI (gcloud)** instalado
3. **Docker** instalado localmente (para testes)
4. **Projeto criado no Google Cloud Console**

## 🚀 Configuração Inicial

### 1. Instalar e Configurar Google Cloud CLI

```bash
# Instalar gcloud (se ainda não tiver)
# macOS:
brew install --cask google-cloud-sdk

# Inicializar e fazer login
gcloud init
gcloud auth login
gcloud auth application-default login

# Configurar projeto
gcloud config set project SEU_PROJECT_ID
```

### 2. Habilitar APIs Necessárias

```bash
# Habilitar APIs do Google Cloud
gcloud services enable \
    run.googleapis.com \
    cloudbuild.googleapis.com \
    artifactregistry.googleapis.com \
    secretmanager.googleapis.com \
    sqladmin.googleapis.com
```

### 3. Criar Artifact Registry

```bash
# Criar repositório para imagens Docker
gcloud artifacts repositories create laravel-apps \
    --repository-format=docker \
    --location=us-central1 \
    --description="Laravel application images"

# Configurar autenticação do Docker
gcloud auth configure-docker us-central1-docker.pkg.dev
```

### 4. Configurar Banco de Dados

#### Opção A: Cloud SQL (Recomendado para produção)

```bash
# Criar instância PostgreSQL
gcloud sql instances create laravel-db \
    --database-version=POSTGRES_15 \
    --tier=db-f1-micro \
    --region=us-central1

# Criar database
gcloud sql databases create laravel --instance=laravel-db

# Criar usuário
gcloud sql users create laravel-user \
    --instance=laravel-db \
    --password=SENHA_SEGURA

# Para MySQL, use:
# gcloud sql instances create laravel-db \
#     --database-version=MYSQL_8_0 \
#     --tier=db-f1-micro \
#     --region=us-central1
```

#### Opção B: Database Externo

Você pode usar qualquer banco de dados acessível pela internet (ex: PlanetScale, Supabase, etc.)

### 5. Configurar Secrets

```bash
# Gerar APP_KEY do Laravel (se ainda não tiver)
php artisan key:generate --show

# Criar secrets no Secret Manager
echo -n "base64:SUA_APP_KEY_AQUI" | gcloud secrets create APP_KEY --data-file=-
echo -n "senha_do_banco" | gcloud secrets create DB_PASSWORD --data-file=-

# Adicionar mais secrets conforme necessário
echo -n "seu-session-secret" | gcloud secrets create SESSION_SECRET --data-file=-
```

## 📦 Build e Deploy Manual

### 1. Testar Build Localmente

```bash
# Build da imagem Docker
docker build -t laravel-app:test .

# Testar localmente
docker run -p 8080:8080 \
    -e APP_KEY="base64:SUA_APP_KEY" \
    -e DB_CONNECTION=sqlite \
    -e APP_ENV=local \
    laravel-app:test
```

### 2. Build e Push para Artifact Registry

```bash
# Definir variáveis
PROJECT_ID=$(gcloud config get-value project)
REGION="us-central1"
SERVICE_NAME="laravel-app"

# Build e tag da imagem
docker build -t ${REGION}-docker.pkg.dev/${PROJECT_ID}/laravel-apps/${SERVICE_NAME}:latest .

# Push para Artifact Registry
docker push ${REGION}-docker.pkg.dev/${PROJECT_ID}/laravel-apps/${SERVICE_NAME}:latest
```

### 3. Deploy no Cloud Run

```bash
# Deploy básico
gcloud run deploy laravel-app \
    --image=${REGION}-docker.pkg.dev/${PROJECT_ID}/laravel-apps/${SERVICE_NAME}:latest \
    --region=us-central1 \
    --platform=managed \
    --allow-unauthenticated \
    --memory=512Mi \
    --cpu=1 \
    --timeout=300 \
    --min-instances=0 \
    --max-instances=10 \
    --set-env-vars="APP_ENV=production,APP_DEBUG=false,LOG_CHANNEL=stderr,SESSION_DRIVER=cookie,QUEUE_CONNECTION=sync" \
    --set-secrets="APP_KEY=APP_KEY:latest"
```

### 4. Configurar Variáveis de Ambiente Adicionais

```bash
# Atualizar service com todas as variáveis de ambiente
gcloud run services update laravel-app \
    --region=us-central1 \
    --set-env-vars="
APP_ENV=production,
APP_DEBUG=false,
APP_URL=https://seu-dominio.app,
LOG_CHANNEL=stderr,
SESSION_DRIVER=database,
SESSION_LIFETIME=120,
CACHE_DRIVER=database,
QUEUE_CONNECTION=database,
DB_CONNECTION=pgsql,
DB_HOST=/cloudsql/PROJECT_ID:REGION:INSTANCE_NAME,
DB_PORT=5432,
DB_DATABASE=laravel,
DB_USERNAME=laravel-user,
FILESYSTEM_DISK=gcs,
GCS_BUCKET=seu-bucket-name
" \
    --set-secrets="APP_KEY=APP_KEY:latest,DB_PASSWORD=DB_PASSWORD:latest"
```

### 5. Conectar ao Cloud SQL

```bash
# Adicionar Cloud SQL ao service
gcloud run services update laravel-app \
    --region=us-central1 \
    --add-cloudsql-instances=PROJECT_ID:REGION:INSTANCE_NAME
```

### 6. Rodar Migrações

```bash
# Opção 1: Via Cloud Run Jobs
gcloud run jobs create laravel-migrate \
    --image=${REGION}-docker.pkg.dev/${PROJECT_ID}/laravel-apps/${SERVICE_NAME}:latest \
    --region=us-central1 \
    --set-env-vars="RUN_MIGRATIONS=true" \
    --set-secrets="APP_KEY=APP_KEY:latest,DB_PASSWORD=DB_PASSWORD:latest" \
    --add-cloudsql-instances=PROJECT_ID:REGION:INSTANCE_NAME \
    --command="php" \
    --args="artisan,migrate,--force"

# Executar job
gcloud run jobs execute laravel-migrate --region=us-central1
```

## 🔄 Deploy Automático com Cloud Build

### 1. Configurar Trigger do GitHub (Recomendado)

```bash
# Conectar repositório GitHub
gcloud builds repositories connect https://github.com/seu-usuario/seu-repo

# Criar trigger
gcloud builds triggers create github \
    --name="deploy-laravel-prod" \
    --repo-name="seu-repo" \
    --repo-owner="seu-usuario" \
    --branch-pattern="^main$" \
    --build-config="cloudbuild.yaml"
```

### 2. Ou Build Manual com Cloud Build

```bash
# Submit build para Cloud Build
gcloud builds submit \
    --config=cloudbuild.yaml \
    --substitutions=_SERVICE_NAME=laravel-app,_REGION=us-central1
```

## 🗄️ Storage de Arquivos

### Configurar Google Cloud Storage

```bash
# Criar bucket
gsutil mb -p ${PROJECT_ID} -c STANDARD -l us-central1 gs://seu-bucket-laravel/

# Configurar CORS (se necessário para uploads diretos)
cat > cors.json << EOF
[
  {
    "origin": ["https://seu-dominio.app"],
    "method": ["GET", "HEAD", "PUT", "POST", "DELETE"],
    "responseHeader": ["Content-Type"],
    "maxAgeSeconds": 3600
  }
]
EOF

gsutil cors set cors.json gs://seu-bucket-laravel/

# Instalar pacote Laravel para GCS
composer require superbalist/laravel-google-cloud-storage
```

## 🔒 Configurações de Segurança

### 1. Configurar Domínio Personalizado

```bash
# Mapear domínio customizado
gcloud run domain-mappings create \
    --service=laravel-app \
    --domain=seu-dominio.app \
    --region=us-central1
```

### 2. Configurar IAM e Permissões

```bash
# Dar permissões ao Cloud Run para acessar Secret Manager
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
    --role="roles/secretmanager.secretAccessor"

# Permissões para Cloud SQL
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
    --role="roles/cloudsql.client"

# Permissões para Cloud Storage
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
    --role="roles/storage.objectAdmin"
```

## 📊 Monitoramento e Logs

### Ver Logs

```bash
# Logs em tempo real
gcloud run services logs tail laravel-app --region=us-central1

# Logs no Console
# https://console.cloud.google.com/logs
```

### Métricas

```bash
# Ver métricas no Cloud Console
# https://console.cloud.google.com/run
```

## 🔧 Troubleshooting

### Container não inicia

```bash
# Verificar logs
gcloud run services logs read laravel-app --region=us-central1 --limit=50

# Testar localmente
docker run -it --entrypoint=/bin/sh laravel-app:latest
```

### Problemas de permissão

```bash
# Verificar se storage está com permissões corretas
php artisan storage:link
```

### Timeout de requests

```bash
# Aumentar timeout
gcloud run services update laravel-app \
    --region=us-central1 \
    --timeout=300
```

## 💰 Otimização de Custos

```bash
# Usar min-instances=0 para escalar para zero quando não há tráfego
gcloud run services update laravel-app \
    --region=us-central1 \
    --min-instances=0 \
    --max-instances=10

# Configurar CPU allocation
gcloud run services update laravel-app \
    --region=us-central1 \
    --cpu-throttling  # CPU only during request processing
```

## 📝 Checklist de Deploy

- [ ] APIs do Google Cloud habilitadas
- [ ] Artifact Registry criado
- [ ] Banco de dados configurado (Cloud SQL ou externo)
- [ ] Secrets criados no Secret Manager
- [ ] APP_KEY gerado e armazenado
- [ ] Imagem Docker buildada e enviada
- [ ] Service deployado no Cloud Run
- [ ] Variáveis de ambiente configuradas
- [ ] Cloud SQL conectado (se aplicável)
- [ ] Migrações executadas
- [ ] Storage configurado (se necessário)
- [ ] Domínio personalizado mapeado
- [ ] SSL/HTTPS configurado
- [ ] Logs e monitoramento verificados

## 🔗 Links Úteis

- [Cloud Run Documentation](https://cloud.google.com/run/docs)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Cloud SQL Pricing](https://cloud.google.com/sql/pricing)
- [Cloud Run Pricing](https://cloud.google.com/run/pricing)

## 🆘 Suporte

Para problemas específicos do Laravel:
- Documentação: https://laravel.com/docs
- Fórum: https://laracasts.com/discuss

Para problemas do Google Cloud:
- Support: https://cloud.google.com/support
- Community: https://www.googlecloudcommunity.com/

