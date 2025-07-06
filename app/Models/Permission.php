<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';

    protected $fillable = [
        'permission_name'
    ];

    /**
     * Relation avec PermissionMenu
     */
    public function permissionMenus()
    {
        return $this->hasMany(PermissionMenu::class, 'permission_id');
    }

    /**
     * Relation avec les rôles via PermissionMenu
     */
    public function roles()
    {
        return $this->hasManyThrough(
            Role::class,
            PermissionMenu::class,
            'permission_id', // Clé étrangère sur permission_menu
            'id', // Clé étrangère sur role_permission
            'id', // Clé locale sur permission
            'id' // Clé locale sur permission_menu
        );
    }

    /**
     * Relation avec les menus via PermissionMenu
     */
    public function menus()
    {
        return $this->hasManyThrough(
            Menu::class,
            PermissionMenu::class,
            'permission_id', // Clé étrangère sur permission_menu
            'id', // Clé locale sur menu
            'id', // Clé locale sur permission
            'menu_id' // Clé étrangère sur permission_menu
        );
    }
}
