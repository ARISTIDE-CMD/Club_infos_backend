<?php

// Chemin vers l'autoloader généré par Composer
require __DIR__ . '/../vendor/autoload.php';

// Charge les variables d'environnement si elles ne sont pas définies (pour le développement local Vercel)
if (file_exists(__DIR__ . '/../.env')) {
    (new \Dotenv\Dotenv(__DIR__ . '/../'))->load();
}

/*
|--------------------------------------------------------------------------
| Créer l'Application
|--------------------------------------------------------------------------
|
| Ici, nous obtenons l'instance de l'application qui sert de conteneur
| central pour tous les composants du framework.
|
*/
$app = require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Lancer l'Application
|--------------------------------------------------------------------------
|
| Nous lançons le kernel HTTP pour gérer la requête entrante et envoyer la réponse.
| Le builder Vercel-PHP gère l'environnement serverless.
|
*/

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);