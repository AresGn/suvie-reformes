<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Login;
use App\Models\User;
use App\Models\Personne;
use App\Models\Role;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $personne = Personne::where('email', $request->email)->first();

        if ($personne && $personne->user && Hash::check($request->password, $personne->user->pwd)) {
            $user = $personne->user;

            Auth::login($user, $request->filled('remember'));

            // Charger les rôles et permissions de l'utilisateur en session
            $this->loadUserRolesAndPermissions($user);

            // Déclencher manuellement l'événement Login pour assurer la création de session
            event(new Login('web', $user, $request->filled('remember')));

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Identifiants invalides.']);
    }

    public function logout()
    {
        // Nettoyer les données de session liées aux rôles et permissions
        session()->forget(['user_roles', 'user_permissions', 'user_menus']);

        Auth::logout();
        return redirect()->route('login');
    }


    /**
     * Charge les rôles et permissions de l'utilisateur en session
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
}

