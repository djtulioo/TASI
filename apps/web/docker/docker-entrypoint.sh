#!/bin/bash
set -e

echo "Iniciando aplicação Laravel..."

# Rodar migrações (se necessário)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Rodando migrações..."
    php artisan migrate --force --no-interaction
fi

# Otimizações do Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

