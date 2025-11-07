# Image PHP CLI
FROM php:8.2-cli

# Installer dépendances système
RUN apt-get update && apt-get install -y unzip git libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Définir le répertoire de travail
WORKDIR /app

# Copier le code
COPY . .

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Droits pour storage et bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Exposer le port attendu par Render
EXPOSE 10000

# Commande de démarrage : migrations + serveur Laravel
CMD bash -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}"
