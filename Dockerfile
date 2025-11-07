# -------------------------------
# Dockerfile Laravel optimisé
# -------------------------------

# 1️⃣ Image PHP CLI officielle (Laravel API + Artisan Serve)
FROM php:8.2-cli

# 2️⃣ Installer dépendances système + extensions PHP
RUN apt-get update && apt-get install -y unzip git libzip-dev curl \
    && docker-php-ext-install pdo pdo_mysql zip

# 3️⃣ Définir le répertoire de travail
WORKDIR /app

# 4️⃣ Copier le code source
COPY . .

# 5️⃣ Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# 6️⃣ Droits pour storage et bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# 7️⃣ Exposer le port attendu par Render
EXPOSE 10000

# 8️⃣ Commande de démarrage : migrations + serveur Laravel
CMD bash -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}"
