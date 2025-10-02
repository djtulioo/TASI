#!/bin/bash

# Script para atualizar variáveis de ambiente no Cloud Run
# Uso: ./update-env.sh

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${YELLOW}ℹ${NC} $1"
}

# Configurações
PROJECT_ID=$(gcloud config get-value project 2>/dev/null)
REGION="${REGION:-us-central1}"
SERVICE_NAME="${SERVICE_NAME:-laravel-app}"

if [ -z "$PROJECT_ID" ]; then
    print_error "Projeto do Google Cloud não configurado!"
    echo "Execute: gcloud config set project SEU_PROJECT_ID"
    exit 1
fi

print_info "Projeto: $PROJECT_ID"
print_info "Região: $REGION"
print_info "Service: $SERVICE_NAME"
echo ""

# Verificar se o arquivo .env.cloudrun existe
if [ ! -f ".env.cloudrun" ]; then
    print_error "Arquivo .env.cloudrun não encontrado!"
    echo "Copie o arquivo .env.cloudrun.example e configure os valores:"
    echo "  cp .env.cloudrun.example .env.cloudrun"
    echo "  # Edite .env.cloudrun com suas configurações"
    exit 1
fi

print_info "Lendo variáveis de .env.cloudrun..."

# Ler variáveis do arquivo e construir string de env vars
ENV_VARS=""
SECRETS=""

while IFS='=' read -r key value; do
    # Ignorar comentários e linhas vazias
    [[ "$key" =~ ^#.*$ ]] && continue
    [[ -z "$key" ]] && continue

    # Remover espaços em branco
    key=$(echo "$key" | xargs)
    value=$(echo "$value" | xargs)

    # Remover aspas do valor se existirem
    value="${value%\"}"
    value="${value#\"}"

    # Variáveis sensíveis que devem usar Secret Manager
    if [[ "$key" == "APP_KEY" ]] || [[ "$key" == "DB_PASSWORD" ]] || [[ "$key" == "MAIL_PASSWORD" ]]; then
        if [[ ! -z "$value" ]]; then
            SECRETS="${SECRETS},${key}=${key}:latest"
            print_info "Secret detectado: $key (será usado do Secret Manager)"
        fi
    else
        if [[ ! -z "$value" ]]; then
            if [[ -z "$ENV_VARS" ]]; then
                ENV_VARS="${key}=${value}"
            else
                ENV_VARS="${ENV_VARS},${key}=${value}"
            fi
        fi
    fi
done < .env.cloudrun

# Remover vírgula inicial dos secrets
SECRETS="${SECRETS#,}"

print_info "Atualizando Cloud Run Service..."
echo ""

# Construir comando
CMD="gcloud run services update $SERVICE_NAME --region=$REGION"

if [[ ! -z "$ENV_VARS" ]]; then
    CMD="$CMD --set-env-vars=\"$ENV_VARS\""
    print_info "Variáveis de ambiente encontradas"
fi

if [[ ! -z "$SECRETS" ]]; then
    CMD="$CMD --set-secrets=\"$SECRETS\""
    print_info "Secrets encontrados"
fi

# Executar comando
eval $CMD

if [ $? -eq 0 ]; then
    print_success "Variáveis de ambiente atualizadas com sucesso!"

    URL=$(gcloud run services describe $SERVICE_NAME --region=$REGION --format='value(status.url)')
    print_success "Service URL: $URL"
else
    print_error "Erro ao atualizar variáveis de ambiente"
    exit 1
fi

