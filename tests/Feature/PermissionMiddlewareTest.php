<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\PermissionMenu;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminRole;
    protected $userRole;
    protected $permission;
    protected $menu;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer des données de test
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->adminRole = Role::create([
            'role_name' => 'admin',
            'description' => 'Administrator role'
        ]);

        $this->userRole = Role::create([
            'role_name' => 'user',
            'description' => 'Regular user role'
        ]);

        $this->permission = Permission::create([
            'permission_name' => 'read_reformes'
        ]);

        $this->menu = Menu::create([
            'libelle' => 'Test Menu',
            'url' => '/test',
            'icon' => 'test-icon',
            'ordre' => 1
        ]);

        // Créer des routes de test
        Route::middleware(['auth', 'role:admin'])->get('/admin-only', function () {
            return response()->json(['message' => 'Admin access granted']);
        });

        Route::middleware(['auth', 'role.permission:permission:read_reformes'])->get('/permission-test', function () {
            return response()->json(['message' => 'Permission access granted']);
        });

        Route::middleware(['auth', 'role.permission:menu:' . $this->menu->id])->get('/menu-test', function () {
            return response()->json(['message' => 'Menu access granted']);
        });
    }

    public function test_role_middleware_allows_correct_role()
    {
        // Associer le rôle admin à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);

        // Tester l'accès avec le bon rôle
        $response = $this->actingAs($this->user)->get('/admin-only');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Admin access granted']);
    }

    public function test_role_middleware_denies_wrong_role()
    {
        // Associer un rôle non-admin à l'utilisateur
        $this->user->roles()->attach($this->userRole->id);

        // Tester l'accès avec le mauvais rôle
        $response = $this->actingAs($this->user)->get('/admin-only');
        $response->assertStatus(403);
    }

    public function test_role_middleware_denies_unauthenticated_user()
    {
        // Tester l'accès sans authentification
        $response = $this->get('/admin-only');
        $response->assertRedirect('/login');
    }

    public function test_permission_middleware_allows_correct_permission()
    {
        // Créer l'association permission-menu-rôle
        PermissionMenu::create([
            'permission_id' => $this->permission->id,
            'menu_id' => $this->menu->id,
            'role_id' => $this->adminRole->id
        ]);

        // Associer le rôle à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);

        // Tester l'accès avec la bonne permission
        $response = $this->actingAs($this->user)->get('/permission-test');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Permission access granted']);
    }

    public function test_permission_middleware_denies_wrong_permission()
    {
        // Associer un rôle sans la permission requise
        $this->user->roles()->attach($this->userRole->id);

        // Tester l'accès sans la permission
        $response = $this->actingAs($this->user)->get('/permission-test');
        $response->assertStatus(403);
    }

    public function test_menu_middleware_allows_correct_menu_access()
    {
        // Créer l'association permission-menu-rôle
        PermissionMenu::create([
            'permission_id' => $this->permission->id,
            'menu_id' => $this->menu->id,
            'role_id' => $this->adminRole->id
        ]);

        // Associer le rôle à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);

        // Tester l'accès au menu
        $response = $this->actingAs($this->user)->get('/menu-test');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Menu access granted']);
    }

    public function test_menu_middleware_denies_wrong_menu_access()
    {
        // Associer un rôle sans accès au menu
        $this->user->roles()->attach($this->userRole->id);

        // Tester l'accès au menu sans permission
        $response = $this->actingAs($this->user)->get('/menu-test');
        $response->assertStatus(403);
    }

    public function test_middleware_with_multiple_roles()
    {
        // Créer une route qui accepte plusieurs rôles
        Route::middleware(['auth', 'role:admin,user'])->get('/multi-role', function () {
            return response()->json(['message' => 'Multi-role access granted']);
        });

        // Tester avec le rôle admin
        $this->user->roles()->attach($this->adminRole->id);
        $response = $this->actingAs($this->user)->get('/multi-role');
        $response->assertStatus(200);

        // Tester avec le rôle user
        $this->user->roles()->detach();
        $this->user->roles()->attach($this->userRole->id);
        $response = $this->actingAs($this->user)->get('/multi-role');
        $response->assertStatus(200);
    }

    public function test_middleware_json_response_for_api()
    {
        // Créer une route API
        Route::middleware(['auth', 'role:admin'])->get('/api/admin-only', function () {
            return response()->json(['message' => 'API Admin access granted']);
        });

        // Tester l'accès sans le bon rôle avec une requête JSON
        $this->user->roles()->attach($this->userRole->id);
        $response = $this->actingAs($this->user)
                         ->withHeaders(['Accept' => 'application/json'])
                         ->get('/api/admin-only');
        
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Accès refusé. Rôle requis : admin']);
    }
}
