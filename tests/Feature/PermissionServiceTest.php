<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\PermissionMenu;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $permissionService;
    protected $user;
    protected $adminRole;
    protected $permission;
    protected $menu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissionService = app(PermissionService::class);

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

        $this->permission = Permission::create([
            'permission_name' => 'read_reformes'
        ]);

        $this->menu = Menu::create([
            'libelle' => 'Test Menu',
            'url' => '/test',
            'icon' => 'test-icon',
            'ordre' => 1
        ]);

        // Créer l'association
        PermissionMenu::create([
            'permission_id' => $this->permission->id,
            'menu_id' => $this->menu->id,
            'role_id' => $this->adminRole->id
        ]);

        // Associer le rôle à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);
    }

    public function test_permission_service_checks_role()
    {
        // Tester avec utilisateur spécifique
        $this->assertTrue($this->permissionService->hasRole('admin', $this->user));
        $this->assertFalse($this->permissionService->hasRole('user', $this->user));

        // Tester avec utilisateur connecté
        Auth::login($this->user);
        $this->assertTrue($this->permissionService->hasRole('admin'));
        $this->assertFalse($this->permissionService->hasRole('user'));
    }

    public function test_permission_service_checks_permission()
    {
        // Tester avec utilisateur spécifique
        $this->assertTrue($this->permissionService->hasPermission('read_reformes', $this->user));
        $this->assertFalse($this->permissionService->hasPermission('write_reformes', $this->user));

        // Tester avec utilisateur connecté
        Auth::login($this->user);
        $this->assertTrue($this->permissionService->hasPermission('read_reformes'));
        $this->assertFalse($this->permissionService->hasPermission('write_reformes'));
    }

    public function test_permission_service_checks_menu_access()
    {
        // Tester avec utilisateur spécifique
        $this->assertTrue($this->permissionService->canAccessMenu($this->menu->id, $this->user));

        // Tester avec utilisateur connecté
        Auth::login($this->user);
        $this->assertTrue($this->permissionService->canAccessMenu($this->menu->id));
    }

    public function test_permission_service_checks_admin()
    {
        // Tester avec utilisateur spécifique
        $this->assertTrue($this->permissionService->isAdmin($this->user));

        // Tester avec utilisateur connecté
        Auth::login($this->user);
        $this->assertTrue($this->permissionService->isAdmin());
    }

    public function test_permission_service_crud_permissions()
    {
        // Créer des permissions CRUD
        Permission::create(['permission_name' => 'create_reformes']);
        Permission::create(['permission_name' => 'update_reformes']);
        Permission::create(['permission_name' => 'delete_reformes']);

        // Créer les associations
        foreach (['create_reformes', 'update_reformes', 'delete_reformes'] as $permName) {
            $perm = Permission::where('permission_name', $permName)->first();
            PermissionMenu::create([
                'permission_id' => $perm->id,
                'menu_id' => $this->menu->id,
                'role_id' => $this->adminRole->id
            ]);
        }

        Auth::login($this->user);

        // Tester les méthodes CRUD
        $this->assertTrue($this->permissionService->canRead('reformes'));
        $this->assertTrue($this->permissionService->canCreate('reformes'));
        $this->assertTrue($this->permissionService->canUpdate('reformes'));
        $this->assertTrue($this->permissionService->canDelete('reformes'));

        // Tester une ressource inexistante
        $this->assertFalse($this->permissionService->canCreate('inexistant'));
    }

    public function test_permission_service_with_session_data()
    {
        Auth::login($this->user);

        // Simuler des données en session
        session([
            'user_roles' => ['admin'],
            'user_permissions' => ['read_reformes'],
            'user_menus' => [
                ['id' => $this->menu->id, 'libelle' => 'Test Menu']
            ]
        ]);

        // Les vérifications doivent utiliser les données de session
        $this->assertTrue($this->permissionService->hasRole('admin'));
        $this->assertTrue($this->permissionService->hasPermission('read_reformes'));
        $this->assertTrue($this->permissionService->canAccessMenu($this->menu->id));
    }

    public function test_permission_service_without_authenticated_user()
    {
        // Sans utilisateur connecté
        $this->assertFalse($this->permissionService->hasRole('admin'));
        $this->assertFalse($this->permissionService->hasPermission('read_reformes'));
        $this->assertFalse($this->permissionService->canAccessMenu($this->menu->id));
        $this->assertFalse($this->permissionService->isAdmin());
    }

    public function test_permission_service_get_accessible_menus()
    {
        Auth::login($this->user);

        $accessibleMenus = $this->permissionService->getAccessibleMenus();

        $this->assertNotEmpty($accessibleMenus);
        $this->assertTrue($accessibleMenus->contains('id', $this->menu->id));
    }
}
