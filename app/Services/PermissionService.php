<?php

namespace App\Services;

use App\Models\User;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * Durée de cache en minutes
     */
    const CACHE_DURATION = 60;

    /**
     * Vérifie si l'utilisateur connecté a un rôle spécifique
     */
    public function hasRole($roleName, $user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return false;
        }

        // Vérifier d'abord en session pour les performances
        $sessionRoles = session('user_roles', []);
        if (in_array($roleName, $sessionRoles)) {
            return true;
        }

        // Fallback sur la base de données
        return $user->hasRole($roleName);
    }

    /**
     * Vérifie si l'utilisateur connecté a l'une des permissions spécifiées
     */
    public function hasPermission($permissionName, $user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return false;
        }

        // Vérifier d'abord en session pour les performances
        $sessionPermissions = session('user_permissions', []);
        if (in_array($permissionName, $sessionPermissions)) {
            return true;
        }

        // Fallback sur la base de données
        return $user->hasPermission($permissionName);
    }

    /**
     * Vérifie si l'utilisateur peut accéder à un menu spécifique
     */
    public function canAccessMenu($menuId, $user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return false;
        }

        // Vérifier d'abord en session pour les performances
        $sessionMenus = session('user_menus', []);
        foreach ($sessionMenus as $menu) {
            if ($menu['id'] == $menuId) {
                return true;
            }
        }

        // Fallback sur la base de données
        return $user->canAccessMenu($menuId);
    }

    /**
     * Récupère tous les menus accessibles par l'utilisateur
     */
    public function getAccessibleMenus($user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return collect();
        }

        // Utiliser le cache pour améliorer les performances
        $cacheKey = "user_menus_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($user) {
            return $user->getAccessibleMenus();
        });
    }

    /**
     * Récupère toutes les permissions de l'utilisateur
     */
    public function getUserPermissions($user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return collect();
        }

        // Utiliser le cache pour améliorer les performances
        $cacheKey = "user_permissions_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($user) {
            return $user->getAllPermissions();
        });
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    public function isAdmin($user = null)
    {
        return $this->hasRole('admin', $user) || $this->hasRole('administrateur', $user);
    }

    /**
     * Nettoie le cache des permissions pour un utilisateur
     */
    public function clearUserCache($userId)
    {
        Cache::forget("user_menus_{$userId}");
        Cache::forget("user_permissions_{$userId}");
    }

    /**
     * Recharge les permissions d'un utilisateur en session
     */
    public function reloadUserPermissions($user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return;
        }

        // Nettoyer le cache
        $this->clearUserCache($user->id);

        // Recharger en session
        $this->loadUserRolesAndPermissions($user);
    }

    /**
     * Charge les rôles et permissions de l'utilisateur en session
     * (Méthode similaire à celle du AuthController)
     */
    private function loadUserRolesAndPermissions($user)
    {
        // Charger les rôles avec leurs relations
        $roles = $user->roles()->with('permissionMenus.menu', 'permissionMenus.permission')->get();
        
        // Extraire les noms des rôles
        $roleNames = $roles->pluck('role_name')->toArray();
        
        // Extraire les permissions uniques
        $permissions = collect();
        $menus = collect();
        
        foreach ($roles as $role) {
            foreach ($role->permissionMenus as $permissionMenu) {
                if ($permissionMenu->permission) {
                    $permissions->push($permissionMenu->permission->permission_name);
                }
                if ($permissionMenu->menu) {
                    $menus->push([
                        'id' => $permissionMenu->menu->id,
                        'libelle' => $permissionMenu->menu->libelle,
                        'url' => $permissionMenu->menu->url,
                        'icon' => $permissionMenu->menu->icon,
                    ]);
                }
            }
        }
        
        // Stocker en session pour un accès rapide
        session([
            'user_roles' => $roleNames,
            'user_permissions' => $permissions->unique()->values()->toArray(),
            'user_menus' => $menus->unique('id')->values()->toArray(),
        ]);
    }

    /**
     * Vérifie les permissions pour une action spécifique
     */
    public function checkAction($action, $resource = null, $user = null)
    {
        $user = $user ?: Auth::user();
        
        if (!$user) {
            return false;
        }

        // Construire le nom de la permission
        $permissionName = $resource ? "{$action}_{$resource}" : $action;
        
        return $this->hasPermission($permissionName, $user);
    }

    /**
     * Vérifie si l'utilisateur peut effectuer une action CRUD
     */
    public function canCreate($resource, $user = null)
    {
        return $this->checkAction('create', $resource, $user);
    }

    public function canRead($resource, $user = null)
    {
        return $this->checkAction('read', $resource, $user);
    }

    public function canUpdate($resource, $user = null)
    {
        return $this->checkAction('update', $resource, $user);
    }

    public function canDelete($resource, $user = null)
    {
        return $this->checkAction('delete', $resource, $user);
    }
}
