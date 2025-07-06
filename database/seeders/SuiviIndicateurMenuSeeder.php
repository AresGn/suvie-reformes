<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\PermissionMenu;
use App\Models\Role;
use App\Models\RolePermission;

class SuiviIndicateurMenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Créer le menu principal pour le suivi des indicateurs
        $menu = Menu::create([
            'libelle' => 'Suivi des Indicateurs',
            'url' => '/suivi-indicateurs',
            'icon' => 'educate-icon educate-analytics'
        ]);

        // 2. Créer les permissions pour le suivi des indicateurs
        $permissions = [
            [
                'name' => 'view_suivi_indicateurs',
                'description' => 'Voir le suivi des indicateurs'
            ],
            [
                'name' => 'manage_suivi_indicateurs',
                'description' => 'Gérer le suivi des indicateurs'
            ],
            [
                'name' => 'create_evolution_indicateurs',
                'description' => 'Créer des évolutions d\'indicateurs'
            ],
            [
                'name' => 'edit_evolution_indicateurs',
                'description' => 'Modifier des évolutions d\'indicateurs'
            ],
            [
                'name' => 'delete_evolution_indicateurs',
                'description' => 'Supprimer des évolutions d\'indicateurs'
            ],
            [
                'name' => 'export_indicateurs',
                'description' => 'Exporter les données d\'indicateurs'
            ],
            [
                'name' => 'import_indicateurs',
                'description' => 'Importer des données d\'indicateurs'
            ]
        ];

        $createdPermissions = [];
        foreach ($permissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                ['description' => $permissionData['description']]
            );
            $createdPermissions[] = $permission;
        }

        // 3. Associer les permissions au menu
        foreach ($createdPermissions as $permission) {
            PermissionMenu::firstOrCreate([
                'menu_id' => $menu->id,
                'permission_id' => $permission->id
            ]);
        }

        // 4. Attribuer les permissions aux rôles appropriés
        $roles = [
            'admin' => ['view_suivi_indicateurs', 'manage_suivi_indicateurs', 'create_evolution_indicateurs', 'edit_evolution_indicateurs', 'delete_evolution_indicateurs', 'export_indicateurs', 'import_indicateurs'],
            'gestionnaire' => ['view_suivi_indicateurs', 'manage_suivi_indicateurs', 'create_evolution_indicateurs', 'edit_evolution_indicateurs', 'export_indicateurs', 'import_indicateurs'],
            'utilisateur' => ['view_suivi_indicateurs']
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            
            if ($role) {
                foreach ($rolePermissions as $permissionName) {
                    $permission = Permission::where('name', $permissionName)->first();
                    $permissionMenu = PermissionMenu::where('menu_id', $menu->id)
                        ->where('permission_id', $permission->id)
                        ->first();
                    
                    if ($permission && $permissionMenu) {
                        RolePermission::firstOrCreate([
                            'role_id' => $role->id,
                            'permission_menu_id' => $permissionMenu->id
                        ]);
                    }
                }
            }
        }

        $this->command->info('Menu et permissions pour le suivi des indicateurs créés avec succès !');
    }
}
