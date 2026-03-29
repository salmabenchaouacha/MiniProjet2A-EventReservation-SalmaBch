FROM php:8.2-apache

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    default-mysql-client \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    intl \
    zip \
    opcache \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer depuis l’image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Config Apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copier les fichiers du projet
COPY . /var/www/html

# Installer dépendances PHP
RUN composer install --no-interaction

# Droits
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public