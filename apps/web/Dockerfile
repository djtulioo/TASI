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
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configurar PHP-FPM
RUN echo "pm.max_children = 50" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.start_servers = 5" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.min_spare_servers = 5" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "pm.max_spare_servers = 35" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Configurar PHP para produção
COPY docker/laravel.ini /usr/local/etc/php/conf.d/laravel.ini

# Configurar Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script de inicialização
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
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

