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

class UserPermissionTest extends TestCase
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
    }

    public function test_user_can_have_roles()
    {
        // Associer un rôle à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);

        // Vérifier que l'utilisateur a le rôle
        $this->assertTrue($this->user->hasRole('admin'));
        $this->assertFalse($this->user->hasRole('user'));
    }

    public function test_user_can_have_multiple_roles()
    {
        // Associer plusieurs rôles
        $this->user->roles()->attach([$this->adminRole->id, $this->userRole->id]);

        // Vérifier que l'utilisateur a les deux rôles
        $this->assertTrue($this->user->hasRole('admin'));
        $this->assertTrue($this->user->hasRole('user'));
        $this->assertTrue($this->user->hasAnyRole(['admin', 'user']));
    }

    public function test_user_can_access_menu_through_permission()
    {
        // Créer l'association permission-menu-rôle
        PermissionMenu::create([
            'permission_id' => $this->permission->id,
            'menu_id' => $this->menu->id,
            'role_id' => $this->adminRole->id
        ]);

        // Associer le rôle à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);

        // Vérifier que l'utilisateur peut accéder au menu
        $this->assertTrue($this->user->canAccessMenu($this->menu->id));
    }

    public function test_user_has_permission_through_role()
    {
        // Créer l'association permission-menu-rôle
        PermissionMenu::create([
            'permission_id' => $this->permission->id,
            'menu_id' => $this->menu->id,
            'role_id' => $this->adminRole->id
        ]);

        // Associer le rôle à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);

        // Vérifier que l'utilisateur a la permission
        $this->assertTrue($this->user->hasPermission('read_reformes'));
        $this->assertFalse($this->user->hasPermission('write_reformes'));
    }

    public function test_user_can_get_accessible_menus()
    {
        // Créer plusieurs menus et permissions
        $menu2 = Menu::create([
            'libelle' => 'Test Menu 2',
            'url' => '/test2',
            'icon' => 'test-icon-2',
            'ordre' => 2
        ]);

        $permission2 = Permission::create([
            'permission_name' => 'read_activites'
        ]);

        // Créer les associations
        PermissionMenu::create([
            'permission_id' => $this->permission->id,
            'menu_id' => $this->menu->id,
            'role_id' => $this->adminRole->id
        ]);

        PermissionMenu::create([
            'permission_id' => $permission2->id,
            'menu_id' => $menu2->id,
            'role_id' => $this->adminRole->id
        ]);

        // Associer le rôle à l'utilisateur
        $this->user->roles()->attach($this->adminRole->id);

        // Récupérer les menus accessibles
        $accessibleMenus = $this->user->getAccessibleMenus();

        // Vérifier que l'utilisateur a accès aux deux menus
        $this->assertCount(2, $accessibleMenus);
        $this->assertTrue($accessibleMenus->contains('id', $this->menu->id));
        $this->assertTrue($accessibleMenus->contains('id', $menu2->id));
    }

    public function test_user_is_admin()
    {
        // Tester avec un rôle admin
        $this->user->roles()->attach($this->adminRole->id);
        $this->assertTrue($this->user->isAdmin());

        // Tester avec un rôle non-admin
        $this->user->roles()->detach();
        $this->user->roles()->attach($this->userRole->id);
        $this->assertFalse($this->user->isAdmin());
    }

    public function test_user_without_roles_has_no_permissions()
    {
        // Utilisateur sans rôle
        $this->assertFalse($this->user->hasRole('admin'));
        $this->assertFalse($this->user->hasPermission('read_reformes'));
        $this->assertFalse($this->user->canAccessMenu($this->menu->id));
        $this->assertFalse($this->user->isAdmin());
    }
}
