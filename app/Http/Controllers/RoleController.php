<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Role;
use App\Models\PermissionMenu;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
{
    $roles = Role::with('permissionMenus.menu', 'permissionMenus.permission')->get();
    $menus = Menu::with('permissionMenus.permission')->get();

    //dd($roles);
    foreach ($roles as $role) {
        $role->permissions_html = "<ul>";
        foreach($role->permissionMenus as $pm) {
            $role->permissions_html .= "<li>" . ($pm->menu->libelle ?? 'Menu supprimé') . " - " . ($pm->permission->permission_name ?? 'Permission supprimée') . "</li>";
        }
        $role->permissions_html .= "</ul>";
    }

    return view('role', compact('roles', 'menus'));
}

    public function create()
    {
        $menus = Menu::with(['permissionMenus.permission'])->get();
        return view('role', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|unique:role,role_name',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'role_name' => $request->role_name,
        ]);

        $role->permissionMenus()->sync($request->permissions);

        return redirect()->route('role')->with('success', 'Rôle créé avec succès.');
    }

    public function show($id)
    {
        $role = Role::with('permissionMenus.permission', 'permissionMenus.menu')->findOrFail($id);
        return view('role.show', compact('role'));
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $menus = Menu::with(['permissionMenus.permission'])->get();
        $rolePermissionIds = $role->permissionMenus->pluck('id')->toArray();

        return view('role.edit', compact('role', 'menus', 'rolePermissionIds'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'role_name' => 'required|unique:role,role_name,' . $id,
            'permissions' => 'array',
        ]);

        $role->update([
            'role_name' => $request->role_name,
        ]);

        $role->permissionMenus()->sync($request->permissions);

        return redirect()->route('role')->with('success', 'Rôle mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->permissionMenus()->detach(); // détache les relations
        $role->delete();

        return redirect()->route('role')->with('success', 'Rôle supprimé avec succès.');
    }
}
