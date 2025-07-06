<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = ['libelle', 'url', 'icon'];

    /**
     * Relation avec PermissionMenu
     */
    public function permissionMenus()
    {
        return $this->hasMany(PermissionMenu::class, 'menu_id');
    }

    /**
     * Relation avec les permissions via PermissionMenu
     */
    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            PermissionMenu::class,
            'menu_id', // Clé étrangère sur permission_menu
            'id', // Clé locale sur permission
            'id', // Clé locale sur menu
            'permission_id' // Clé étrangère sur permission_menu
        );
    }

    /**
     * Relation avec les rôles via PermissionMenu et RolePermission
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permission',
            'permission_menu_id',
            'role_id'
        )->join('permission_menu', 'permission_menu.id', '=', 'role_permission.permission_menu_id')
         ->where('permission_menu.menu_id', $this->id);
    }

    /**
     * Vérifie si un utilisateur a accès à ce menu
     */
    public function isAccessibleBy($user)
    {
        if (!$user) {
            return false;
        }

        // Récupère tous les rôles de l'utilisateur
        $userRoles = $user->roles()->pluck('role.id');

        // Vérifie si l'un des rôles de l'utilisateur a accès à ce menu
        return $this->roles()->whereIn('role.id', $userRoles)->exists();
    }
}
