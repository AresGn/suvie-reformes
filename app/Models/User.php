<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'personne_id',
        'pwd',
        'status',
    ];

    protected $hidden = [
        'pwd',
    ];

    // Utiliser le bon champ de mot de passe
    public function getAuthPassword()
    {
        return $this->pwd;
    }

    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personne_id');
    }

    /**
     * Retourne la personne associée ou lance une exception si absente.
     */
    public function personneOrFail()
    {
        if (!$this->personne) {
            throw new \Exception('Personne liée introuvable pour l\'utilisateur ID=' . $this->id);
        }
        return $this->personne;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role', 'id_user', 'role_id');
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }

    /**
     * Vérifie si l'utilisateur a l'un des rôles spécifiés
     */
    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return $this->roles()->whereIn('role_name', $roles)->exists();
    }

    /**
     * Vérifie si l'utilisateur a tous les rôles spécifiés
     */
    public function hasAllRoles($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        $userRoles = $this->roles()->pluck('role_name')->toArray();

        return count(array_intersect($roles, $userRoles)) === count($roles);
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permissionName)
    {
        return $this->roles()
            ->join('role_permission', 'role.id', '=', 'role_permission.role_id')
            ->join('permission_menu', 'role_permission.permission_menu_id', '=', 'permission_menu.id')
            ->join('permission', 'permission_menu.permission_id', '=', 'permission.id')
            ->where('permission.permission_name', $permissionName)
            ->exists();
    }

    /**
     * Vérifie si l'utilisateur peut accéder à un menu spécifique
     */
    public function canAccessMenu($menuId)
    {
        return $this->roles()
            ->join('role_permission', 'role.id', '=', 'role_permission.role_id')
            ->join('permission_menu', 'role_permission.permission_menu_id', '=', 'permission_menu.id')
            ->where('permission_menu.menu_id', $menuId)
            ->exists();
    }

    /**
     * Vérifie si l'utilisateur peut accéder à une URL spécifique
     */
    public function canAccessUrl($url)
    {
        return $this->roles()
            ->join('role_permission', 'role.id', '=', 'role_permission.role_id')
            ->join('permission_menu', 'role_permission.permission_menu_id', '=', 'permission_menu.id')
            ->join('menu', 'permission_menu.menu_id', '=', 'menu.id')
            ->where('menu.url', $url)
            ->exists();
    }

    /**
     * Récupère tous les menus accessibles par l'utilisateur
     */
    public function getAccessibleMenus()
    {
        return Menu::whereIn('id', function($query) {
            $query->select('permission_menu.menu_id')
                  ->from('role_permission')
                  ->join('permission_menu', 'role_permission.permission_menu_id', '=', 'permission_menu.id')
                  ->whereIn('role_permission.role_id', $this->roles()->pluck('role.id'));
        })->get();
    }

    /**
     * Récupère toutes les permissions de l'utilisateur
     */
    public function getAllPermissions()
    {
        return Permission::whereIn('id', function($query) {
            $query->select('permission_menu.permission_id')
                  ->from('role_permission')
                  ->join('permission_menu', 'role_permission.permission_menu_id', '=', 'permission_menu.id')
                  ->whereIn('role_permission.role_id', $this->roles()->pluck('role.id'));
        })->get();
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    public function isAdmin()
    {
        return $this->hasRole('admin') || $this->hasRole('administrateur');
    }

    // Les autres relations sessions...
}
