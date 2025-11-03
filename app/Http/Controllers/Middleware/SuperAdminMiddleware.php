<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Vérification de l'authentification
        if (!auth()->check()) {
            // L'utilisateur n'est pas connecté
            return redirect('/login');
            // Si vous utilisez une API, renvoyez une erreur 401:
            // return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // 2. Vérification de l'autorisation (Logique à adapter)
        // Ceci suppose que votre modèle User a une colonne 'is_superadmin' (ou 'role')
        if (auth()->user()->is_superadmin !== 1) {
            // L'utilisateur est connecté mais n'est pas super-administrateur
            abort(403, 'Accès non autorisé : vous devez être Super Administrateur.');
        }

        // 3. Poursuite de la requête si toutes les vérifications passent
        return $next($request);
    }
}
