<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$parameters): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::user();

        // Si aucun paramètre n'est fourni, laisser passer (middleware d'authentification simple)
        if (empty($parameters)) {
            return $next($request);
        }

        // Analyser les paramètres
        $requiredRoles = [];
        $requiredPermissions = [];
        $requiredMenus = [];

        foreach ($parameters as $parameter) {
            if (strpos($parameter, 'role:') === 0) {
                $requiredRoles[] = substr($parameter, 5);
            } elseif (strpos($parameter, 'permission:') === 0) {
                $requiredPermissions[] = substr($parameter, 11);
            } elseif (strpos($parameter, 'menu:') === 0) {
                $requiredMenus[] = substr($parameter, 5);
            }
        }

        // Vérifier les rôles requis
        if (!empty($requiredRoles)) {
            if (!$user->hasAnyRole($requiredRoles)) {
                return $this->accessDenied($request);
            }
        }

        // Vérifier les permissions requises
        if (!empty($requiredPermissions)) {
            $hasPermission = false;
            foreach ($requiredPermissions as $permission) {
                if ($user->hasPermission($permission)) {
                    $hasPermission = true;
                    break;
                }
            }
            if (!$hasPermission) {
                return $this->accessDenied($request);
            }
        }

        // Vérifier l'accès aux menus requis
        if (!empty($requiredMenus)) {
            $hasMenuAccess = false;
            foreach ($requiredMenus as $menuId) {
                if ($user->canAccessMenu($menuId)) {
                    $hasMenuAccess = true;
                    break;
                }
            }
            if (!$hasMenuAccess) {
                return $this->accessDenied($request);
            }
        }

        return $next($request);
    }

    /**
     * Gère le refus d'accès
     */
    private function accessDenied(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Accès refusé',
                'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.'
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
    }
}
