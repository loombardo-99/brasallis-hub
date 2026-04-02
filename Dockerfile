FROM php:8.2-apache

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Instalar extensões PHP comuns para ERPs
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd mysqli

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Ajustar permissões (se necessário)
RUN chown -R www-data:www-data /var/www/html
