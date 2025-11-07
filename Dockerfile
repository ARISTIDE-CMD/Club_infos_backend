# Image PHP officielle avec Apache
FROM php:8.2-apache

# Installe les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    && docker-php-ext-install pdo pdo_mysql zip

# Active mod_rewrite pour Apache
RUN a2enmod rewrite

# Copie les fichiers du projet
COPY . /var/www/html

# Définit le répertoire de travail
WORKDIR /var/www/html

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installe les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Donne les droits au dossier storage et bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose le port 8080 (Render)
EXPOSE 8080

# Commande de démarrage
CMD bash -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT"


