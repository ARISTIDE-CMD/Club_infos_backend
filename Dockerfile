# 1️⃣ STAGE DE CONSTRUCTION (Basé sur PHP CLI pour installer Composer)
FROM php:8.2-cli AS builder

WORKDIR /app
COPY . .

# Installer les dépendances du builder (git, zip, etc.)
RUN apt-get update && apt-get install -y unzip git libzip-dev curl libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 2️⃣ STAGE FINAL DE PRODUCTION (Basé sur PHP + Apache)
FROM php:8.2-apache

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du stage de construction
COPY --from=builder /app /var/www/html

# Mettre à jour les dépendances de production (si nécessaire) et nettoyer
RUN apt-get update && apt-get install -y libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Configurer Apache pour pointer vers le dossier public de Laravel
# Note : Vous aurez besoin de créer un fichier de configuration Apache (e.g., 000-default.conf)
# pour définir le DocumentRoot à /var/www/html/public.
# Sans ce fichier, Apache pointera vers la racine et exposera tous les fichiers.

# Droits pour storage et bootstrap/cache (www-data est l'utilisateur Apache par défaut)
RUN chown -R www-data:www-data storage bootstrap/cache

# 7️⃣ Exposer le port par défaut d'Apache
EXPOSE 80

# 8️⃣ Commande de démarrage
# Render s'attend à ce que le port d'écoute soit 10000.
# Dans la configuration Render de votre service web, définissez le PORT interne à 80.

# Mettez la commande de migration dans "Start Command" ou "Pre-Deploy Command" sur Render.
# CMD ["apache2-foreground"] # Apache est le CMD par défaut de cette image