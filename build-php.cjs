const { execSync } = require('child_process');

// Ce script est maintenant renommé en .cjs pour être interprété comme CommonJS
// et pouvoir utiliser la fonction require()

try {
    console.log("Starting Composer dependencies installation...");

    // 1. Télécharger l'installeur de Composer
    // 2. Exécuter l'installeur pour obtenir composer.phar
    // 3. Exécuter l'installation des dépendances Composer
    const command = 'php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');" && php composer-setup.php --install-dir=. --filename=composer.phar && php composer.phar install --no-dev --prefer-dist';
    
    // Exécuter la commande dans le shell et afficher la sortie
    execSync(command, { stdio: 'inherit' });

    console.log("Composer installation and dependency fetching completed successfully.");
    // Vercel vérifie le code de sortie. 0 = Succès.
    process.exit(0);

} catch (error) {
    console.error("Composer build failed:", error.message);
    // 1 = Échec.
    process.exit(1);
}