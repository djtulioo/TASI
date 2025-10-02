#!/bin/bash

# Script auxiliar para deploy no Google Cloud Run
# Uso: ./deploy.sh [ambiente] [opções]

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funções auxiliares
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${YELLOW}ℹ${NC} $1"
}

# Verificar se gcloud está instalado
if ! command -v gcloud &> /dev/null; then
    print_error "Google Cloud SDK não está instalado!"
    echo "Instale com: brew install --cask google-cloud-sdk"
    exit 1
fi

# Configurações
PROJECT_ID=$(gcloud config get-value project 2>/dev/null)
REGION="${REGION:-us-central1}"
SERVICE_NAME="${SERVICE_NAME:-laravel-app}"
REPOSITORY="${REPOSITORY:-laravel-apps}"

if [ -z "$PROJECT_ID" ]; then
    print_error "Projeto do Google Cloud não configurado!"
    echo "Execute: gcloud config set project SEU_PROJECT_ID"
    exit 1
fi

print_info "Projeto: $PROJECT_ID"
print_info "Região: $REGION"
print_info "Service: $SERVICE_NAME"
echo ""

# Menu de opções
case "$1" in
    "setup")
        print_info "Configurando ambiente do Google Cloud..."

        # Habilitar APIs
        print_info "Habilitando APIs necessárias..."
        gcloud services enable \
            run.googleapis.com \
            cloudbuild.googleapis.com \
            artifactregistry.googleapis.com \
            secretmanager.googleapis.com \
            sqladmin.googleapis.com

        # Criar Artifact Registry
        print_info "Criando Artifact Registry..."
        gcloud artifacts repositories create $REPOSITORY \
            --repository-format=docker \
            --location=$REGION \
            --description="Laravel application images" \
            2>/dev/null || print_info "Repository já existe"

        # Configurar Docker
        print_info "Configurando autenticação do Docker..."
        gcloud auth configure-docker ${REGION}-docker.pkg.dev

        print_success "Setup concluído!"
        ;;

    "build")
        print_info "Buildando imagem Docker..."

        TAG="${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPOSITORY}/${SERVICE_NAME}:latest"

        docker build -t $TAG .

        print_success "Build concluído!"
        print_info "Tag: $TAG"
        ;;

    "push")
        print_info "Enviando imagem para Artifact Registry..."

        TAG="${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPOSITORY}/${SERVICE_NAME}:latest"

        docker push $TAG

        print_success "Push concluído!"
        ;;

    "deploy")
        print_info "Fazendo deploy no Cloud Run..."

        TAG="${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPOSITORY}/${SERVICE_NAME}:latest"

        gcloud run deploy $SERVICE_NAME \
            --image=$TAG \
            --region=$REGION \
            --platform=managed \
            --allow-unauthenticated \
            --memory=512Mi \
            --cpu=1 \
            --timeout=300 \
            --min-instances=0 \
            --max-instances=10 \
            --set-env-vars="APP_ENV=production,APP_DEBUG=false,LOG_CHANNEL=stderr" \
            --set-secrets="APP_KEY=APP_KEY:latest"

        print_success "Deploy concluído!"

        # Obter URL
        URL=$(gcloud run services describe $SERVICE_NAME --region=$REGION --format='value(status.url)')
        print_success "Aplicação disponível em: $URL"
        ;;

    "all")
        print_info "Executando build, push e deploy completo..."

        # Build
        TAG="${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPOSITORY}/${SERVICE_NAME}:latest"
        print_info "1/3 Building..."
        docker build -t $TAG .

        # Push
        print_info "2/3 Pushing..."
        docker push $TAG

        # Deploy
        print_info "3/3 Deploying..."
        gcloud run deploy $SERVICE_NAME \
            --image=$TAG \
            --region=$REGION \
            --platform=managed \
            --allow-unauthenticated \
            --memory=512Mi \
            --cpu=1 \
            --timeout=300 \
            --min-instances=0 \
            --max-instances=10 \
            --set-env-vars="APP_ENV=production,APP_DEBUG=false,LOG_CHANNEL=stderr" \
            --set-secrets="APP_KEY=APP_KEY:latest"

        URL=$(gcloud run services describe $SERVICE_NAME --region=$REGION --format='value(status.url)')
        print_success "Deploy completo! URL: $URL"
        ;;

    "logs")
        print_info "Visualizando logs..."
        gcloud run services logs tail $SERVICE_NAME --region=$REGION
        ;;

    "migrate")
        print_info "Executando migrações..."

        TAG="${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPOSITORY}/${SERVICE_NAME}:latest"

        # Criar job temporário para executar migrations
        gcloud run jobs create ${SERVICE_NAME}-migrate-$(date +%s) \
            --image=$TAG \
            --region=$REGION \
            --set-env-vars="RUN_MIGRATIONS=true" \
            --set-secrets="APP_KEY=APP_KEY:latest" \
            --execute-now \
            --wait

        print_success "Migrações executadas!"
        ;;

    "env")
        print_info "Atualizando variáveis de ambiente..."

        if [ -z "$2" ]; then
            print_error "Uso: ./deploy.sh env KEY=VALUE [KEY2=VALUE2 ...]"
            exit 1
        fi

        shift
        gcloud run services update $SERVICE_NAME \
            --region=$REGION \
            --set-env-vars="$@"

        print_success "Variáveis atualizadas!"
        ;;

    "secret")
        print_info "Criando secret..."

        if [ -z "$2" ] || [ -z "$3" ]; then
            print_error "Uso: ./deploy.sh secret NOME VALOR"
            exit 1
        fi

        echo -n "$3" | gcloud secrets create $2 --data-file=- 2>/dev/null || \
        echo -n "$3" | gcloud secrets versions add $2 --data-file=-

        print_success "Secret $2 criado/atualizado!"
        ;;

    "status")
        print_info "Status do serviço..."
        gcloud run services describe $SERVICE_NAME --region=$REGION
        ;;

    "url")
        URL=$(gcloud run services describe $SERVICE_NAME --region=$REGION --format='value(status.url)')
        echo $URL
        ;;

    *)
        echo "Script de deploy para Google Cloud Run"
        echo ""
        echo "Uso: ./deploy.sh [comando] [opções]"
        echo ""
        echo "Comandos disponíveis:"
        echo "  setup      - Configura ambiente inicial do Google Cloud"
        echo "  build      - Builda a imagem Docker"
        echo "  push       - Envia imagem para Artifact Registry"
        echo "  deploy     - Faz deploy no Cloud Run"
        echo "  all        - Executa build, push e deploy"
        echo "  logs       - Visualiza logs em tempo real"
        echo "  migrate    - Executa migrações do banco"
        echo "  env        - Atualiza variáveis de ambiente"
        echo "  secret     - Cria/atualiza secret"
        echo "  status     - Mostra status do serviço"
        echo "  url        - Mostra URL da aplicação"
        echo ""
        echo "Exemplos:"
        echo "  ./deploy.sh setup"
        echo "  ./deploy.sh all"
        echo "  ./deploy.sh env APP_DEBUG=false LOG_LEVEL=error"
        echo "  ./deploy.sh secret APP_KEY base64:xyz123..."
        echo ""
        exit 1
        ;;
esac

