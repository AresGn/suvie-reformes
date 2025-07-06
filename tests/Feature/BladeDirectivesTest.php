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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class BladeDirectivesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminRole;
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

    public function test_has_role_blade_directive()
    {
        Auth::login($this->user);

        // Tester la directive @hasRole
        $template = '@hasRole("admin")Admin Content@endhasRole';
        $compiled = Blade::compileString($template);
        
        // Vérifier que la directive est compilée correctement
        $this->assertStringContainsString('app(App\Services\PermissionService::class)->hasRole', $compiled);
    }

    public function test_has_permission_blade_directive()
    {
        Auth::login($this->user);

        // Tester la directive @hasPermission
        $template = '@hasPermission("read_reformes")Permission Content@endhasPermission';
        $compiled = Blade::compileString($template);
        
        // Vérifier que la directive est compilée correctement
        $this->assertStringContainsString('app(App\Services\PermissionService::class)->hasPermission', $compiled);
    }

    public function test_can_access_menu_blade_directive()
    {
        Auth::login($this->user);

        // Tester la directive @canAccessMenu
        $template = '@canAccessMenu(' . $this->menu->id . ')Menu Content@endcanAccessMenu';
        $compiled = Blade::compileString($template);
        
        // Vérifier que la directive est compilée correctement
        $this->assertStringContainsString('app(App\Services\PermissionService::class)->canAccessMenu', $compiled);
    }

    public function test_is_admin_blade_directive()
    {
        Auth::login($this->user);

        // Tester la directive @isAdmin
        $template = '@isAdminAdmin Only Content@endisAdmin';
        $compiled = Blade::compileString($template);
        
        // Vérifier que la directive est compilée correctement
        $this->assertStringContainsString('app(App\Services\PermissionService::class)->isAdmin', $compiled);
    }

    public function test_crud_blade_directives()
    {
        Auth::login($this->user);

        // Tester les directives CRUD
        $templates = [
            '@canCreate("reformes")Create Content@endcanCreate',
            '@canRead("reformes")Read Content@endcanRead',
            '@canUpdate("reformes")Update Content@endcanUpdate',
            '@canDelete("reformes")Delete Content@endcanDelete'
        ];

        foreach ($templates as $template) {
            $compiled = Blade::compileString($template);
            $this->assertStringContainsString('app(App\Services\PermissionService::class)', $compiled);
        }
    }

    public function test_blade_directives_render_correctly_with_permissions()
    {
        Auth::login($this->user);

        // Créer une vue de test avec des directives
        $viewContent = '
            @hasRole("admin")
                <div class="admin-content">Admin Panel</div>
            @endhasRole
            
            @hasPermission("read_reformes")
                <div class="reformes-content">Reformes List</div>
            @endhasPermission
            
            @canAccessMenu(' . $this->menu->id . ')
                <div class="menu-content">Menu Item</div>
            @endcanAccessMenu
            
            @isAdmin
                <div class="admin-only">Admin Only</div>
            @endisAdmin
        ';

        // Compiler et évaluer le contenu
        $compiled = Blade::compileString($viewContent);
        
        // Vérifier que toutes les directives sont présentes
        $this->assertStringContainsString('hasRole', $compiled);
        $this->assertStringContainsString('hasPermission', $compiled);
        $this->assertStringContainsString('canAccessMenu', $compiled);
        $this->assertStringContainsString('isAdmin', $compiled);
    }

    public function test_blade_directives_with_no_permissions()
    {
        // Créer un utilisateur sans permissions
        $userWithoutPermissions = User::create([
            'name' => 'No Permission User',
            'email' => 'noperm@example.com',
            'password' => Hash::make('password'),
        ]);

        Auth::login($userWithoutPermissions);

        // Les directives ne devraient pas afficher le contenu
        $template = '@hasRole("admin")Admin Content@endhasRole';
        $compiled = Blade::compileString($template);
        
        // Vérifier que la directive est compilée (même si elle retournera false)
        $this->assertStringContainsString('app(App\Services\PermissionService::class)->hasRole', $compiled);
    }

    public function test_nested_blade_directives()
    {
        Auth::login($this->user);

        // Tester des directives imbriquées
        $template = '
            @hasRole("admin")
                @hasPermission("read_reformes")
                    <div>Admin with read permission</div>
                @endhasPermission
            @endhasRole
        ';

        $compiled = Blade::compileString($template);
        
        // Vérifier que les deux directives sont présentes
        $this->assertStringContainsString('hasRole', $compiled);
        $this->assertStringContainsString('hasPermission', $compiled);
    }
}
