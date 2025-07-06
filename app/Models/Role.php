<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use app\Models\PermissionMenu;
class Role extends Model
{

    protected $table = 'role'; 
   

    protected $fillable = ['role_name'];

    // public function permissions()
    // {
    //     return $this->belongsToMany(PermissionMenu::class, 'role_permission');
    // }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role', 'role_id', 'id_user');
    }

    //public function permissionMenus()
   // {
       // return $this->belongsToMany(PermissionMenu::class, 'role_permission', 'role_id', 'permission_menu_id');
    //}


    //public function permissionMenus()
    //{
        //return $this->hasMany(PermissionMenu::class, 'role_id');
    //}

    public function permissionMenus()
{
    return $this->belongsToMany(PermissionMenu::class, 'role_permission', 'role_id', 'permission_menu_id')
                ->with('menu', 'permission');
}



}