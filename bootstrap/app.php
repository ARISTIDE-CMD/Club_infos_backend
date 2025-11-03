<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SuperAdminMiddleware; // <-- PENSEZ à importer la classe si vous le souhaitez

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // 1. Enregistrez les aliases pour les middlewares de route
        // Ces aliases sont utilisés dans vos fichiers de routes (ex: ->middleware('superadmin'))
        $middleware->alias([
            'superadmin' => SuperAdminMiddleware::class,
            // 'auth' => \App\Http\Middleware\Authenticate::class, // (Optionnel, généralement déjà inclus)
        ]);

        // 2. Si vous aviez des middlewares globaux (qui s'appliquent à toutes les requêtes)
        // $middleware->web([
        //     \App\Http\Middleware\EncryptCookies::class,
        // ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
