# Dockerfile multi-stage otimizado para Laravel com Inertia e Vue.js no Cloud Run

# Stage 1: Instalar dependências do Composer
FROM composer:2 AS composer-builder

WORKDIR /app

# Copiar arquivos de dependências do Composer
COPY composer.json composer.lock ./

# Instalar dependências do PHP (sem dev)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Stage 2: Build dos assets frontend com Node.js
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copiar arquivos de dependências
COPY package*.json ./
COPY jsconfig.json ./

# Instalar dependências do Node
RUN npm ci --prefer-offline --no-audit

# Copiar código fonte necessário para build
COPY resources ./resources
COPY tailwind.config.js ./
COPY postcss.config.js ./
COPY vite.config.js ./
COPY public ./public

# Copiar vendor do Composer (necessário para Ziggy)
COPY --from=composer-builder /app/vendor ./vendor

# Build dos assets para produção
RUN npm run build

# Stage 3: Imagem final com PHP e Laravel
FROM php:8.2-fpm-alpine

# Instalar dependências do sistema necessárias para Laravel
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    mysql-client \
    sqlite-libs \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    oniguruma-dev \
    bash \
    curl

# Instalar extensões PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar o código da aplicação
COPY . .

# Copiar as dependências do Composer já instaladas
COPY --from=composer-builder /app/vendor ./vendor

# Copiar os assets buildados do stage anterior
COPY --from=node-builder /app/public/build ./public/build

# Finalizar instalação do Composer (autoload otimizado)
RUN composer dump-autoload --optimize --classmap-authoritative

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configurar Nginx
COPY <<'EOF' /etc/nginx/nginx.conf
user www-data;
worker_processes auto;
pid /run/nginx.pid;
error_log /dev/stderr warn;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /dev/stdout main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    server {
        listen 8080;
        server_name _;
        root /var/www/html/public;

        index index.php;

        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }

        error_page 404 /index.php;

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
            fastcgi_hide_header X-Powered-By;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
}
EOF

# Configurar PHP-FPM
RUN echo "pm.max_children = 50" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.start_servers = 5" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.min_spare_servers = 5" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.max_spare_servers = 35" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Configurar PHP para produção
COPY <<'EOF' /usr/local/etc/php/conf.d/laravel.ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
expose_php = Off

; OPcache settings
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.fast_shutdown=1
EOF

# Configurar Supervisor
COPY <<'EOF' /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

# Script de inicialização
COPY <<'EOF' /usr/local/bin/docker-entrypoint.sh
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
EOF

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expor porta 8080 (Cloud Run usa esta porta)
EXPOSE 8080

# Usar o usuário www-data para segurança (exceto supervisor que precisa de root)
# USER www-data

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8080/ || exit 1

# Executar script de inicialização
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

