<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::user();

        // Si aucun rôle n'est spécifié, laisser passer
        if (empty($roles)) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a l'un des rôles requis
        if (!$user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Accès refusé',
                    'message' => 'Vous n\'avez pas le rôle nécessaire pour accéder à cette ressource.'
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Vous n\'avez pas le rôle nécessaire pour accéder à cette page.');
        }

        return $next($request);
    }
}
