const { execSync } = require('child_process');
const path = require('path');

console.log('--- Démarrage de la phase de construction PHP ---');

try {
    // 1. Définir la chaîne de commande complète
    // Nous utilisons 'php' car à ce stade, le builder Vercel-PHP devrait l'avoir rendu disponible.
    const command = `
        # 1. Télécharger et installer Composer
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php
        
        # 2. Installer les dépendances Laravel
        php composer.phar install --no-dev --prefer-dist
        
        # 3. Nettoyer les fichiers d'installation
        php -r "unlink('composer-setup.php');"
        
        # 4. Préparer l'application Laravel
        php artisan key:generate
        php artisan config:clear
    `;

    // 2. Exécuter la commande dans l'environnement Shell
    execSync(command, { 
        stdio: 'inherit',
        shell: '/bin/bash' // S'assurer qu'on utilise un shell compatible avec les retours à la ligne
    });

    console.log('--- Construction PHP terminée avec succès. ---');

} catch (error) {
    console.error('Erreur critique pendant la construction PHP.');
    // Sortir avec un code d'erreur pour faire échouer le déploiement Vercel
    process.exit(1); 
}