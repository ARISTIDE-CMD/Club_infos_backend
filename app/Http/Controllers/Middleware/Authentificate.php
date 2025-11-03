<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'superadmin') {
            return response()->json(['message' => 'Accès refusé. Super Admin uniquement.'], 403);
        }

        return $next($request);
    }
}
