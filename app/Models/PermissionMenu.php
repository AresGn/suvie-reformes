<?php

namespace App\Models;
use App\Models\Role ;
use App\Models\Permission ;

use Illuminate\Database\Eloquent\Model;

class PermissionMenu extends Model
{

    protected $table = 'permission_menu';

    protected $fillable = ['id','menu_id','permission_id', 'created_by'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }


}
