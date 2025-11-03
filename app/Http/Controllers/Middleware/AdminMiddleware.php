<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Project;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé. Admin uniquement.'], 403);
        }

        // Ajouter une propriété pour filtrer les projets/étudiants plus tard
        $request->admin = $user;

        return $next($request);
    }
}
